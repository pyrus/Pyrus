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
        if ($path) {
            if ($path != ':memory:') {
                if (dirname($path . '.pear2registry') != $path) {
                    $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
                }
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
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite registry, registry is read-only');
            }
            @mkdir(dirname($path), 0755, true);
        }
        if ($readonly && !file_exists($path)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite registry, registry is read-only');
        }
        self::$databases[$path] = new SQLiteDatabase($path, 0666, $error);
        if (!self::$databases[$path]) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot open SQLite registry: ' . $error);
        }
        if (@self::$databases[$path]->singleQuery('SELECT version FROM pearregistryversion') == '1.0.0') {
            if (!self::$databases[$path]->singleQuery('SELECT COUNT(*) FROM channels')) {
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
        if (!$strict && $a = self::$databases[$this->_path]->singleQuery('SELECT channel FROM channels WHERE
              alias="' . sqlite_escape_string($channel) . '"')) {
            return true;
        }
        if ($a = self::$databases[$this->_path]->singleQuery('SELECT channel FROM channels WHERE
              channel="' . sqlite_escape_string($channel) . '"')) {
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
        if (self::$databases[$this->_path]->singleQuery('SELECT channel FROM channels WHERE channel="' .
              $channel->getName() . '"')) {
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
        if (!@self::$databases[$this->_path]->queryExec('
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
            )
            ')) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the registry');
        }
        if (!@self::$databases[$this->_path]->queryExec('
            INSERT INTO channel_servers
            (channel, server, ssl, port, xmlrpcpath, soappath)
            VALUES(
            "' . $channel->getName() . '",
            "' . $channel->getName() . '",
            ' . ($channel->getSSL() ? 1 : '0') . ',
            ' . $channel->getPort() . ',
            "' . sqlite_escape_string($channel->getSummary('xmlrpc')) . '",
            "' . sqlite_escape_string($channel->getPath('soap')) . '"
            )
            ')) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the registry');
        }
        foreach (array('xmlrpc', 'soap', 'rest') as $protocol) {
            $functions = $channel->getFunctions($protocol);
            if (!$functions) {
                continue;
            }
            if (!is_array($functions)) {
                $functions = array($functions);
            }
            $attrib = $protocol == 'rest' ? 'type' : 'version';
            foreach ($functions as $function) {
                if (!@self::$databases[$this->_path]->queryExec('
                    INSERT INTO channel_server_' . $protocol . '
                    (channel, server, ' . ($protocol == 'rest' ? 'baseurl' : 'function') .
                     ', ' . $attrib . ')
                    VALUES(
                    "' . $channel->getName() . '",
                    "' . $channel->getName() . '",
                    "' . $function['_content'] . '",
                    "' . $function['attribs'][$attrib] . '"
                    )
                    ')) {
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                        ' could not be added to the registry');
                }
            }
        }

        $mirrors = $channel->mirrors;
        if (count($mirrors)) {
            foreach ($mirrors as $mirror) {
                if (!@self::$databases[$this->_path]->queryExec('
                    INSERT INTO channel_servers
                    (channel, server, ssl, port, xmlrpcpath, soappath)
                    VALUES(
                    "' . $channel->getName() . '",
                    "' . $mirror->getName() . '",
                    ' . ($mirror->getSSL() ? 1 : '0') . ',
                    ' . $mirror->getPort() . ',
                    "' . sqlite_escape_string($mirror->getSummary('xmlrpc')) . '",
                    "' . sqlite_escape_string($mirror->getPath('soap')) . '"
                    )
                    ')) {
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                        ' could not be added to the registry');
                }
                foreach (array('xmlrpc', 'soap', 'rest') as $protocol) {
                    $functions = $mirror->getFunctions($protocol);
                    if (!$functions) {
                        continue;
                    }
                    if (!isset($functions[0])) {
                        $functions = array($functions);
                    }
                    $attrib = $protocol == 'rest' ? 'type' : 'version';
                    foreach ($functions as $function) {
                        if (!@self::$databases[$this->_path]->queryExec('
                            INSERT INTO channel_server_' . $protocol . '
                            (channel, server, ' . ($protocol == 'rest' ? 'baseurl' : 'function') .
                             ', ' . $attrib . ')
                            VALUES(
                            "' . $channel->getName() . '",
                            "' . $mirror->getName() . '",
                            "' . $function['_content'] . '",
                            "' . $function['attribs'][$attrib] . '"
                            )
                            ')) {
                            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                                ' could not be added to the registry');
                        }
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
        if ($this->exists($channel, $strict)) {
            return new PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite(self::$databases[$this->_path], $channel);
        } else {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
        }
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
        if (!@self::$databases[$this->_path]->queryExec('DELETE FROM channels WHERE channel="' .
              sqlite_escape_string($channel->getName()) . '"', $error)) {
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
        foreach (self::$databases[$this->_path]->arrayQuery('SELECT channel FROM channels
            ORDER BY channel
        ', SQLITE_NUM) as $res) {
            $ret[] = $res[0];
        }
        return $ret;
    }
}
