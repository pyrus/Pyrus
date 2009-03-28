<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Sqlite3
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * An implementation of a Pyrus channel registry using Sqlite3 as the storage
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelRegistry_Sqlite3 extends PEAR2_Pyrus_ChannelRegistry_Base
{
    /**
     * The database resource
     *
     * @var SQLite3
     */
    static protected $databases = array();
    protected $readonly;
    private $_path;

    /**
     * Initialize the registry
     *
     * @param unknown_type $path
     */
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        if ($path && $path != ':memory:') {
            if (dirname($path . '.pear2registry') != $path) {
                $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
            }
        }
        $this->_path = $path;
        $this->_init($path, $readonly);
    }

    public function getPath()
    {
        return $this->_path;
    }

    private function _init($path, $readonly)
    {
        if (isset(self::$databases[$path]) && self::$databases[$path]) {
            return;
        }

        if (!$path) {
            $path = ':memory:';
        } elseif (!file_exists(dirname($path))) {
            if ($readonly) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
            }
            @mkdir(dirname($path), 0755, true);
        }

        if ($readonly && !file_exists($path)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
        }

        self::$databases[$path] = new SQLite3($path);
        // hopefully this works
        if (self::$databases[$path]->lastErrorCode()) {
            $temp = self::$databases[$path];
            unset(self::$databases[$path]);
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot open SQLite3 channel registry: ' . $temp->lastErrorMsg());
        }

        $sql = 'SELECT version FROM pearregistryversion';
        if (@self::$databases[$path]->querySingle($sql) == '1.0.0') {
            $sql = 'SELECT COUNT(*) FROM channels';
            if (!self::$databases[$path]->querySingle($sql)) {
                if ($readonly) {
                    throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
                }
                $this->initDefaultChannels();
                return;
            }
            return;
        }

        if ($readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
        }

        $a = new PEAR2_Pyrus_Registry_Sqlite3_Creator;
        $a->create(self::$databases[$path]);
        $this->initDefaultChannels();
    }

    function exists($channel, $strict = true)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        $sql = 'SELECT channel FROM channels WHERE alias = "' . self::$databases[$this->_path]->escapeString($channel) . '"';
        if (!$strict && $a = self::$databases[$this->_path]->querySingle($sql)) {
            return true;
        }

        $sql = 'SELECT channel FROM channels WHERE channel = "' . self::$databases[$this->_path]->escapeString($channel) . '"';
        if ($a = self::$databases[$this->_path]->querySingle($sql)) {
            return true;
        }

        return false;
    }

    function add(PEAR2_Pyrus_IChannel $channel, $update = false)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add channel, SQLite3 registry is read-only');
        }

        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        $sql = 'SELECT channel FROM channels WHERE channel = "' . self::$databases[$this->_path]->escapeString($channel->getName()) . '"';
        if (self::$databases[$this->_path]->querySingle($sql)) {
            if (!$update) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                    $channel->getName() . ' has already been discovered');
            }
        } elseif ($update) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->getName() . ' is unknown');
        }

        $validate = $channel->getValidationPackage();

        self::$databases[$this->_path]->exec('BEGIN');
        $sql = '
            INSERT INTO channels
            (channel, summary, suggestedalias, alias, validatepackageversion,
            validatepackage, lastmodified)
            VALUES(
                :name, :summary, :suggestedalias,
                :alias, :version, :package, :lastmodified
            )';

        $stmt = @self::$databases[$this->_path]->prepare($sql);

        $cn = $channel->name;
        $stmt->bindParam(':name',           $cn);
        $cs = $channel->summary;
        $stmt->bindParam(':summary',        $cs);
        $ca = $channel->alias;
        $stmt->bindParam(':suggestedalias', $ca);
        $stmt->bindParam(':alias',          $ca);
        $stmt->bindParam(':version',        $validate['attribs']['version']);
        $stmt->bindParam(':package',        $validate['_content']);
        $mod = serialize($channel->lastModified());
        $stmt->bindParam(':lastmodified',   $mod);

        if (!$stmt->execute()) {
            self::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the SQLite3 registry');
        }
        $stmt->close();

        $sql = '
            INSERT INTO channel_servers
            (channel, server, ssl, port)
            VALUES(
                :channel, :server, :ssl, :port
            )';
        $ssl = 0;
        if ($channel->getSSL()) {
            $ssl = 1;
        }
        $stmt = @self::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':channel', $cn);
        $stmt->bindParam(':server',  $cn);
        $stmt->bindParam(':ssl',     $ssl, SQLITE3_INTEGER);
        $cp = $channel->port;
        $stmt->bindParam(':port',    $cp, SQLITE3_INTEGER);

        if (!$stmt->execute()) {
            self::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the SQLite3 registry');
        }
        $stmt->close();
        
        if ($channel->supportsREST()) {

            foreach ($channel->protocols->rest as $protocol=>$rest) {
                $sql = '
                    INSERT INTO channel_server_rest
                    (channel, server, baseurl, type)
                    VALUES(
                        :channel, :server, :func, :attrib
                    )';
    
                $stmt = @self::$databases[$this->_path]->prepare($sql);
    
                $stmt->bindParam(':channel', $cn);
                $stmt->bindParam(':server',  $cn);
                $u = $rest->baseurl;
                $stmt->bindParam(':func',    $u);
                $stmt->bindParam(':attrib',  $protocol);
    
                if (!$stmt->execute()) {
                    self::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                        ' could not be added to the SQLite3 registry');
                }
                $stmt->close();
            }
    
            foreach ($channel->mirrors as $mirror) {
                $sql = '
                    INSERT INTO channel_servers
                    (channel, server, ssl, port)
                    VALUES(
                        :channel, :server, :ssl, :port
                    )';
    
                $ssl = 0;
                if ($mirror->getSSL()) {
                    $ssl = 1;
                }
                $stmt = @self::$databases[$this->_path]->prepare($sql);
    
                $stmt->bindParam(':channel', $cn);
                $mn = $mirror->getName();
                $stmt->bindParam(':server',  $mn);
                $stmt->bindParam(':ssl',     $ssl, SQLITE3_INTEGER);
                $mp = $mirror->getPort();
                $stmt->bindParam(':port',    $mp, SQLITE3_INTEGER);
    
                if (!$stmt->execute()) {
                    self::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                        ' could not be added to the registry');
                }
                $stmt->close();
    
                foreach ($mirror->protocols->rest as $protocol => $rest) {
                    $sql = '
                        INSERT INTO channel_server_rest
                        (channel, server, baseurl, type)
                        VALUES(
                            :channel, :server, :func, :attrib
                        )';
    
                    $stmt = @self::$databases[$this->_path]->prepare($sql);
    
                    $stmt->bindParam(':channel', $cn);
                    $stmt->bindParam(':server',  $mn);
                    $u = $rest->baseurl;
                    $stmt->bindParam(':func',    $u);
                    $stmt->bindParam(':attrib',  $protocol);
    
                    if (!$stmt->execute()) {
                        self::$databases[$this->_path]->exec('ROLLBACK');
                        throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                            ' could not be added to the SQLite3 registry');
                    }
                    $stmt->close();
                }
            }
        }
        self::$databases[$this->_path]->exec('COMMIT');
    }

    function update(PEAR2_Pyrus_IChannel $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot update channel, SQLite3 registry is read-only');
        }

        return $this->add($channel, true);
    }

    function get($channel, $strict = true)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        if (!$this->exists($channel, $strict)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
        }

        return new PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite3(self::$databases[$this->_path], $channel);
    }

    function delete(PEAR2_Pyrus_IChannel $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete channel, SQLite3 registry is read-only');
        }

        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        $sql = 'DELETE FROM channels WHERE channel = "' . self::$databases[$this->_path]->escapeString($channel->getName()) . '"';
        if (!@self::$databases[$this->_path]->exec($sql)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot delete channel ' .
                $channel->getName() . ': ' . self::$databases[$this->_path]->lastErrorMsg());
        }
    }

    public function listChannels()
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT channel FROM channels ORDER BY channel';
        $res = self::$databases[$this->_path]->query($sql);
        while ($chan = $res->fetchArray(SQLITE_ASSOC)) {
            $ret[] = $chan['channel'];
        }

        return $ret;
    }
}
