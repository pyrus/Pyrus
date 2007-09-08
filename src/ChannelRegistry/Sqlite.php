<?php

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
        }
        $this->database = new SQLiteDatabase($path, 0666, $error);
        if (!$this->database) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot open SQLite registry: ' . $error);
        }
        if (@$this->database->singleQuery('SELECT version FROM pearregistryversion') == '1.0.0') {
            return;
        }
        $a = new PEAR2_Registry_Sqlite_Creator;
        $a->create($this->database);
    }

    function getAlias($channel)
    {
        if ($a = $this->database->singleQuery('SELECT channel FROM channels WHERE
              alias="' . sqlite_escape_string($channel) . '"')) {
            return $a;
        }
        if ($a = $this->database->singleQuery('SELECT channel FROM channels WHERE
              channel="' . sqlite_escape_string($channel) . '"')) {
            return $a;
        }
        throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel/alias: ' . $channel);
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

    function hasMirror($channel, $mirror)
    {
        
    }

    function getObject($channel)
    {
        if (!$this->exists($channel)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot retrieve channel' .
                ' object, channel ' . $channel . ' is not registered');
        }
        $a = new PEAR2_Pyrus_ChannelFile;
    }

    function setAlias($channel, $alias)
    {
        $error = '';
        if (!$this->exists($channel)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot set channel ' .
                $channel . ' alias to ' . $alias . ': Channel is not registered');
        }
        if (!@$this->database->queryExec('UPDATE channels SET alias=\'' .
              sqlite_escape_string($alias) . '\'', $error)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot set channel ' .
                $channel . ' alias to ' . $alias . ': ' . $error);
        }
    }

    function add(PEAR2_Pyrus_ChannelFile $channel)
    {
        if ($this->database->singleQuery('SELECT channel FROM channels WHERE channel="' .
              $channel->getName() . '"')) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->getName() . ' has already been discovered');
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
        $mirrors = $channel->getMirrors();
        if (count($mirrors)) {
            foreach ($mirrors as $mirror) {
                $servers[] = $mirror['attribs']['host'];
                if (!@$this->database->queryExec('
                    INSERT INTO channel_servers
                    (channel, server, ssl, port)
                    VALUES(
                    "' . $channel->getName() . '",
                    "' . $mirror['attribs']['host'] . '",
                    ' . ($channel->getSSL($mirror['attribs']['host']) ? 1 : '0') . ',
                    ' . $channel->getPort($mirror['attribs']['host']) . '
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

    public function update(PEAR2_Pyrus_ChannelFile $channel)
    {
        // for now
        $this->add($channel);
    }

    function getMirrors($channel)
    {
        return $this->database->arrayQuery('SELECT server, ssl, port FROM
            channel_servers WHERE channel = \'' . sqlite_escape_string($channel) .
            '\' AND server <> channel', SQLITE_ASSOC);
    }

    function delete($channel)
    {
        $error = '';
        if (!@$this->database->queryExec('DELETE FROM channels WHERE channel="' .
              sqlite_escape_string($channel) . '"', $error)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot delete channel ' .
                $channel . ': ' . $error);
        }
    }
}