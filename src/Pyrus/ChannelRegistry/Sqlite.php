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
    protected $database;
    private $_path;

    /**
     * Initialize the registry
     *
     * @param unknown_type $path
     */
    function __construct($path)
    {
        if ($path) {
            if ($path != ':memory:') {
                if (dirname($path . '.pear2registry') != $path) {
                    $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
                }
            }
        }
        $this->_init($path);
        $this->_path = $path;
    }

    private function _init($path)
    {
        $error = '';
        if (!$path) {
            $path = ':memory:';
        } elseif (!file_exists(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }
        $this->database = new SQLiteDatabase($path, 0666, $error);
        if (!$this->database) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot open SQLite registry: ' . $error);
        }
        if (@$this->database->singleQuery('SELECT version FROM pearregistryversion') == '1.0.0') {
            return;
        }
        $a = new PEAR2_Pyrus_Registry_Sqlite_Creator;
        $a->create($this->database);
    }

    function exists($channel, $strict = true)
    {
        if (!$strict && $a = $this->database->singleQuery('SELECT channel FROM channels WHERE
              alias="' . sqlite_escape_string($channel) . '"')) {
            return true;
        }
        if ($a = $this->database->singleQuery('SELECT channel FROM channels WHERE
              channel="' . sqlite_escape_string($channel) . '"')) {
            return true;
        }
        return false;
    }

    function add(PEAR2_Pyrus_IChannel $channel, $update = false)
    {
        if ($this->database->singleQuery('SELECT channel FROM channels WHERE channel="' .
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
        $this->database->queryExec('BEGIN');
        if (!@$this->database->queryExec('
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
        if (!@$this->database->queryExec('
            INSERT INTO channel_servers
            (channel, server, ssl, port)
            VALUES(
            "' . $channel->getName() . '",
            "' . $channel->getName() . '",
            ' . ($channel->getSSL() ? 1 : '0') . ',
            ' . $channel->getPort() . '
            )
            ')) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                ' could not be added to the registry');
        }
        $servers = array(false);
        $mirrors = $channel->mirrors;
        if (count($mirrors)) {
            foreach ($mirrors as $mirror) {
                $servers[] = $mirror['attribs']['host'];
                if (!@$this->database->queryExec('
                    INSERT INTO channel_servers
                    (channel, server, ssl, port)
                    VALUES(
                    "' . $channel->getName() . '",
                    "' . $mirror->getName() . '",
                    ' . ($mirror->getSSL() ? 1 : '0') . ',
                    ' . $mirror->getPort() . '
                    )
                    ')) {
                    throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' . $channel->getName() .
                        ' could not be added to the registry');
                }
            }
        }
        foreach ($servers as $server) {
            foreach (array('xmlrpc', 'soap', 'rest') as $protocol) {
                $functions = $channel->getFunctions($protocol, $server);
                if (!$functions) {
                    continue;
                }
                if (!isset($functions[0])) {
                    $functions = array($functions);
                }
                $actualserver = $server ? $server : $channel->getName();
                $attrib = $protocol == 'rest' ? 'type' : 'version';
                foreach ($functions as $function) {
                    if (!@$this->database->queryExec('
                        INSERT INTO channel_server_' . $protocol . '
                        (channel, server, ' . ($protocol == 'rest' ? 'baseurl' : 'function') .
                         ', ' . $attrib . ')
                        VALUES(
                        "' . $channel->getName() . '",
                        "' . $actualserver . '",
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
        $this->database->queryExec('COMMIT');
    }

    function update(PEAR2_Pyrus_IChannel $channel)
    {
        return $this->add($channel, true);
    }

    function get($channel)
    {
        if ($this->exists($channel)) {
            return new PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite($this->database, $channel);
        } else {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
        }
    }

    function delete(PEAR2_Pyrus_IChannel $channel)
    {
        $error = '';
        if (!@$this->database->queryExec('DELETE FROM channels WHERE channel="' .
              sqlite_escape_string($channel->getName()) . '"', $error)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot delete channel ' .
                $channel->getName() . ': ' . $error);
        }
    }

    public function listChannels()
    {
        $ret = array();
        foreach ($this->database->arrayQuery('SELECT channel FROM channels
            ORDER BY channel
        ', SQLITE_NUM) as $res) {
            $ret[] = $res[0];
        }
        return $ret;
    }
}