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
class PEAR2_Pyrus_ChannelRegistry_Sqlite extends PEAR2_Pyrus_ChannelRegistry_Base
{
    /**
     * The database resource
     *
     * @var SQLiteDatabase
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
        $error = '';
        if (!$path) {
            $path = ':memory:';
        } elseif (!file_exists(dirname($path))) {
            if ($readonly) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite channel registry, registry is read-only');
            }
            @mkdir(dirname($path), 0755, true);
        }

        if ($readonly && !file_exists($path)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite channel registry, registry is read-only');
        }

        self::$databases[$path] = new SQLiteDatabase($path, 0666, $error);
        if (!self::$databases[$path]) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot open SQLite channel registry: ' . $error);
        }

        $sql = 'SELECT version FROM pearregistryversion';
        if (@self::$databases[$path]->singleQuery($sql) == '1.0.0') {
            $sql = 'SELECT COUNT(*) FROM channels';
            if (!self::$databases[$path]->singleQuery($sql)) {
                if ($readonly) {
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot create SQLite channel registry, registry is read-only');
                }
                $this->initDefaultChannels();
                return;
            }
            return;
        }

        if ($readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot create SQLite channel registry, registry is read-only');
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

        $sql = 'SELECT channel FROM channels WHERE alias = "' . sqlite_escape_string($channel) . '"';
        if (!$strict && $a = self::$databases[$this->_path]->singleQuery($sql)) {
            return true;
        }

        $sql = 'SELECT channel FROM channels WHERE channel = "' . sqlite_escape_string($channel) . '"';
        if ($a = self::$databases[$this->_path]->singleQuery($sql)) {
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

        $sql = 'SELECT channel FROM channels WHERE channel = "' . sqlite_escape_string($channel->getName()) . '"';
        if (self::$databases[$this->_path]->singleQuery($sql)) {
            if (!$update) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                    $channel->getName() . ' has already been discovered');
            }
        } elseif ($update) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->getName() . ' is unknown');
        }

        $validate = $channel->getValidationPackage();

        self::$databases[$this->_path]->queryExec('BEGIN');
        $sql = '
            INSERT INTO channels
            (channel, summary, suggestedalias, alias, validatepackageversion,
            validatepackage, lastmodified)
            VALUES(
            "' . $channel->getName() . '",
            "' . sqlite_escape_string($channel->getSummary()) . '",
            "' . $channel->getAlias() . '",
            "' . $channel->getAlias() . '",
            "' . $validate['attribs']['version'] . '",
            "' . $validate['_content'] . '",
            \'' . sqlite_escape_string(serialize($channel->lastModified())) . '\'
            )';
        if (!@self::$databases[$this->_path]->queryExec($sql)) {
            @self::$databases[$this->_path]->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the registry: ' . sqlite_error_string(sqlite_last_error()));
        }

        $sql = '
            INSERT INTO channel_servers
            (channel, server, ssl, port)
            VALUES(
            "' . $channel->getName() . '",
            "' . $channel->getName() . '",
            ' . ($channel->getSSL() ? 1 : '0') . ',
            ' . $channel->getPort() . '
            )';
        if (!self::$databases[$this->_path]->queryExec($sql)) {
            @self::$databases[$this->_path]->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the registry');
        }

        $functions = $channel->getFunctions('rest');
        if (!$functions) {
            $functions = array();
        }

        if (!is_array($functions)) {
            $functions = array($functions);
        }

        foreach ($functions as $function) {
            $sql = '
                INSERT INTO channel_server_rest
                (channel, server, baseurl, type)
                VALUES(
                "' . $channel->getName() . '",
                "' . $channel->getName() . '",
                "' . $function['_content'] . '",
                "' . $function['attribs']['type'] . '"
                )';
            if (!@self::$databases[$this->_path]->queryExec($sql)) {
                @self::$databases[$this->_path]->queryExec('ROLLBACK');
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                    ' could not be added to the registry');
            }
        }

        $mirrors = $channel->mirrors;
        if (count($mirrors)) {
            foreach ($mirrors as $mirror) {
                $sql = '
                    INSERT INTO channel_servers
                    (channel, server, ssl, port)
                    VALUES(
                    "' . $channel->getName() . '",
                    "' . $mirror->getName() . '",
                    ' . ($mirror->getSSL() ? 1 : '0') . ',
                    ' . $mirror->getPort() . '
                    )';
                if (!@self::$databases[$this->_path]->queryExec($sql)) {
                    @self::$databases[$this->_path]->queryExec('ROLLBACK');
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                        ' could not be added to the registry');
                }

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
                        "' . $channel->getName() . '",
                        "' . $mirror->getName() . '",
                        "' . $function['_content'] . '",
                        "' . $function['attribs']['type'] . '"
                        )';
                    if (!@self::$databases[$this->_path]->queryExec($sql)) {
                        @self::$databases[$this->_path]->queryExec('ROLLBACK');
                        throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                            ' could not be added to the registry');
                    }
                }
            }
        }

        self::$databases[$this->_path]->queryExec('COMMIT');
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

        $error = '';
        $sql = 'DELETE FROM channels WHERE channel = "' . sqlite_escape_string($channel->getName()) . '"';
        if (!@self::$databases[$this->_path]->queryExec($sql, $error)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot delete channel ' .
                $channel->getName() . ': ' . $error);
        }
    }

    public function listChannels()
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite channel registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT channel FROM channels ORDER BY channel';
        foreach (self::$databases[$this->_path]->arrayQuery($sql, SQLITE_NUM) as $res) {
            $ret[] = $res[0];
        }
        return $ret;
    }
}
