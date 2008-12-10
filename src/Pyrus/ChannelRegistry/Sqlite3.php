<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Sqlite
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
 * An implementation of a Pyrus channel registry using Sqlite as the storage
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
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite registry, registry is read-only');
            }
            @mkdir(dirname($path), 0755, true);
        }

        if ($readonly && !file_exists($path)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite registry, registry is read-only');
        }

        self::$databases[$path] = new SQLite3($path, 0666);
        if (!self::$databases[$path]) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot open SQLite registry: ' . $error);
        }

        $sql = 'SELECT version FROM pearregistryversion';
        if (@self::$databases[$path]->querySingle($sql) == '1.0.0') {
            $sql = 'SELECT COUNT(*) FROM channels';
            if (!self::$databases[$path]->querySingle($sql)) {
                $this->initDefaultChannels();
                return;
            }
            return;
        }

        $a = new PEAR2_Pyrus_Registry_Sqlite_Creator;
        $a->create(self::$databases[$path]);
        $this->initDefaultChannels();
    }

    function exists($channel, $strict = true)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite channel registry for ' . $this->_path);
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
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add channel, registry is read-only');
        }

        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite channel registry for ' . $this->_path);
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

        $stmt->bindParam(':name',           $channel->getName());
        $stmt->bindParam(':summary',        $channel->getSummary());
        $stmt->bindParam(':suggestedalias', $channel->getAlias());
        $stmt->bindParam(':alias',          $channel->getAlias());
        $stmt->bindParam(':version',        $validate['attribs']['version']);
        $stmt->bindParam(':package',        $validate['_content']);
        $stmt->bindParam(':lastmodified',   serialize($channel->lastModified()));

        if (!$stmt->execute()) {
            self::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the registry');
        }
        $stmt->close();

        $sql = '
            INSERT INTO channel_servers
            (channel, server, ssl, port)
            VALUES(
                :channel, :server, :ssl, :port
            )';

        $stmt = @self::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':channel', $channel->getName());
        $stmt->bindParam(':server',  $channel->getName());
        $stmt->bindParam(':ssl',     ($channel->getSSL() ? 1 : '0'), SQLITE3_INTEGER);
        $stmt->bindParam(':port',    $channel->getPort(), SQLITE3_INTEGER);

        if (!$stmt->execute()) {
            self::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the registry');
        }
        $stmt->close();

        $functions = $channel->getFunctions('rest');
        if (!$functions) {
            continue;
        }

        if (!is_array($functions)) {
            $functions = array($functions);
        }

        foreach ($functions as $function) {
            $sql = '
                INSERT INTO channel_server_rest
                (channel, server, baseurl, type)
                VALUES(
                    :channel, :server, :func, :attrib
                )';

            $stmt = @self::$databases[$this->_path]->prepare($sql);

            $stmt->bindParam(':channel', $channel->getName());
            $stmt->bindParam(':server',  $channel->getName());
            $stmt->bindParam(':func',    $function['_content']);
            $stmt->bindParam(':attrib',  $function['attribs']['type']);

            if (!$stmt->execute()) {
                self::$databases[$this->_path]->exec('ROLLBACK');
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                    ' could not be added to the registry');
            }
            $stmt->close();
        }

        $mirrors = $channel->mirrors;
        if (count($mirrors)) {
            foreach ($mirrors as $mirror) {
                $sql = '
                    INSERT INTO channel_servers
                    (channel, server, ssl, port)
                    VALUES(
                        :channel, :server, :ssl, :port
                    )';

                $stmt = @self::$databases[$this->_path]->prepare($sql);

                $stmt->bindParam(':channel', $channel->getName());
                $stmt->bindParam(':server',  $mirror->getName());
                $stmt->bindParam(':ssl',     ($mirror->getSSL() ? 1 : '0'));
                $stmt->bindParam(':port',    $mirror->getPort());

                if (!$stmt->execute()) {
                    self::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                        ' could not be added to the registry');
                }
                $stmt->close();

                $functions = $mirror->getFunctions('rest');
                if (!$functions) {
                    continue;
                }

                if (!isset($functions[0])) {
                    $functions = array($functions);
                }

                foreach ($functions as $function) {
                    $sql = '
                        INSERT INTO channel_server_rest
                        (channel, server, baseurl, type)
                        VALUES(
                            :channel, :server, :func, :attrib
                        )';

                    $stmt = @self::$databases[$this->_path]->prepare($sql);

                    $stmt->bindParam(':channel', $channel->getName());
                    $stmt->bindParam(':server',  $mirror->getName());
                    $stmt->bindParam(':func',    $function['_content']);
                    $stmt->bindParam(':attrib',  $function['attribs']['type']);

                    if (!$stmt->execute()) {
                        self::$databases[$this->_path]->exec('ROLLBACK');
                        throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                            ' could not be added to the registry');
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
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot update channel, registry is read-only');
        }

        return $this->add($channel, true);
    }

    function get($channel, $strict = true)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite channel registry for ' . $this->_path);
        }

        if (!$this->exists($channel, $strict)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
        }

        return new PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite(self::$databases[$this->_path], $channel);
    }

    function delete(PEAR2_Pyrus_IChannel $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete channel, registry is read-only');
        }

        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite channel registry for ' . $this->_path);
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
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite channel registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT channel FROM channels ORDER BY channel';
        $res = self::$databases[$this->_path]->query($sql);
        foreach ($res->arrayFetch(SQLITE_NUM) as $res) {
            $ret[] = $res['channel'];
        }

        return $ret;
    }
}
