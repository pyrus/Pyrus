<?php
class PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite implements PEAR2_Pyrus_IChannel
{
    /**
     * The database resource
     *
     * @var SQLiteDatabase
     */
    protected $database;
    protected $mirror;
    private $_path;
    private $_channelname;

    function __construct(SQLiteDatabase $db, $channel)
    {
        $channel = strtolower($channel);
        $this->database = $db;
        $this->_channelname = $this->mirror = $channel;
        if (!$this->database->singleQuery('SELECT channel FROM channels WHERE
              channel="' . sqlite_escape_string($channel) . '"')) {
            if (!($channel = $this->database->singleQuery('SELECT channel FROM channels WHERE
              alias="' . sqlite_escape_string($channel) . '"'))) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Channel ' .
                    $this->_channelname . ' does not exist');
            }
        }
    }

    function getName()
 	{
 	    return $this->_channelName;
 	}

    function setAlias($alias)
    {
        $error = '';
        if (!@$this->database->queryExec('UPDATE channels SET alias=\'' .
              sqlite_escape_string($alias) . '\'', $error)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot set channel ' .
                $this->_channelname . ' alias to ' . $alias . ': ' . $error);
        }
    }

 	function getSummary()
 	{
 	    return $this->database->singleQuery('SELECT summary FROM channels WHERE ' .
 	          'channel=\'' . sqlite_escape_string($this->_channelname) . '\'');
 	}

    /**
     * @return int|80 port number to connect to
     */
    function getPort()
    {
        return $this->database->singleQuery('SELECT port FROM channel_servers WHERE
 	          channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
    }

 	function getSSL($mirror = false)
 	{
 	    return $this->database->singleQuery('SELECT ssl FROM channel_servers WHERE
 	          channel=\'' . sqlite_escape_string($this->_channelname) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
 	}

    /**
     * @param string xmlrpc or soap
     */
    function getPath($protocol)
    {   
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown protocol: ' .
                $protocol);
        }
        $a = $this->database->singleQuery('SELECT ' . $protocol . 'path FROM channel_servers WHERE
 	          channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
        if (!$a) {
            return $protocol . '.php';
        }
    }

    /**
     * @param string protocol type (xmlrpc, soap)
     * @return array|false
     */
    function getFunctions($protocol)
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown protocol: ' .
                $protocol);
        }
        $functions = $this->database->arrayQuery('
            SELECT * FROM channel_server_' . $protocol . '
            WHERE channel = \'' . sqlite_escape_string($this->_channel) . '\ AND
            server = \'' . sqlite_escape_string($this->mirror) . '\'
        ');
        $ret = array();
        foreach ($functions as $func) {
            $ret[] = array('attribs' => array('version' => $func['version']), '_content' => $func['function']);
        }
        return $ret;
    }

 	function getValidatePackage($packagename)
 	{
 	    $r = $this->database->singleQuery('SELECT validatepackage ' .
 	          'FROM channels WHERE ' .
 	          'channel=\'' . sqlite_escape_string($this->_channelname) . '\'');
        if ($r == $packagename) {
            return 'PEAR2_Pyrus_Validate';
        }
        if ($r == 'PEAR_Validate' || $r == 'PEAR_Validate_PECL') {
            return str_replace('PEAR_', 'PEAR2_Pyrus_', $r);
        }
        return $r;
 	}

    public function getREST()
    {
        $urls = $this->database->arrayQuery('SELECT * FROM channel_server_rest WHERE
 	          channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
        $ret = array();
        foreach ($urls as $url) {
            $ret[] = array('attribs' => array('type' => $url['type']), '_content' => $url['baseurl']);
        }
    }

 	function getValidationObject($package)
 	{
 	    $a = $this->getValidatePackage($package);
 	    return new $a;
 	}

 	function __get($value)
 	{
 	    switch ($value) {
 	        case 'mirrors' :
 	            $ret = array();
 	            foreach ($this->database->arrayQuery('SELECT server FROM channel_servers
 	                  WHERE channel=\'' . sqlite_escape_string($this->_channelname) . '\'
 	                  AND server !=\'' . sqlite_escape_string($this->_channelname) . '\'') as $mirror) {
 	                $ret[$mirror] = new PEAR2_Pyrus_ChannelRegistry_Mirror_Sqlite($this,
 	                                    $this->database, $mirror);
                }
 	    }
 	}

 	public function toChannelObject()
 	{
 	    $a = new PEAR2_Pyrus_Channel;
 	    $a->setName($this->getName());
 	    $a->setSummary($this->getSummary());
 	    $a->setPort($this->getPort());
 	    $a->setSSL($this->getSSL());
 	    $a->setValidationPackage();
 	}

 	public function __toString()
 	{
 	    return $this->toChannelObject()->__toString();
 	}

    function getMirrors()
    {
        return $this->database->arrayQuery('SELECT server, ssl, port FROM
            channel_servers WHERE channel = \'' . sqlite_escape_string($this->_channelname) .
            '\' AND server <> channel', SQLITE_ASSOC);
    }

    public function supportsREST()
    {
        return (bool) $this->database->singleQuery('
            SELECT COUNT(*) FROM channel_server_rest WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'
        ');
    }

    public function supports($protocol, $name = null, $version = '1.0')
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown protocol: ' .
                $protocol);
        }
        if ($name === null) {
            return (bool) $this->database->singleQuery('
                SELECT COUNT(*) FROM channel_server_' . $protocol . ' WHERE
                  channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
     	          server=\'' . sqlite_escape_string($this->mirror) . '\'
            ');
        }
        return (bool) $this->database->singleQuery('
            SELECT COUNT(*) FROM channel_server_' . $protocol . ' WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\' AND
 	          function=\'' . sqlite_escape_string($name) . '\' AND
 	          version=\'' . sqlite_escape_string($version) . '\'
        ');
    }

    /**
     * Get the URL to access a base resource.
     *
     * Hyperlinks in the returned xml will be used to retrieve the proper information
     * needed.  This allows extreme extensibility and flexibility in implementation
     * @param string Resource Type to retrieve
     */
    function getBaseURL($resourceType)
    {
        return $this->database->singleQuery('
            SELECT baseurl FROM channel_server_rest WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\' AND
 	          type=\'' . sqlite_escape_string($resourceType) . '\'
        ');
    }

    /**
     * Empty all xmlrpc definitions
     */
    function resetXmlrpc()
    {
        return $this->database->queryExec('
            DELETE FROM channel_server_xmlrpc WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
    }

    /**
     * Empty all SOAP definitions
     */
    function resetSOAP()
    {
        return $this->database->queryExec('
            DELETE FROM channel_server_soap WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        return $this->database->queryExec('
            DELETE FROM channel_server_rest WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
    }

    function setName($name)
    {
        throw new PEAR2_Pyrus_ChannelRegistry_Exception(
            'Cannot change channel name of a registered channel');
    }

    function setPort($port)
    {
        return $this->database->queryExec('
            UPDATE channel_servers SET port=\'' . sqlite_escape_string($port) . '\'WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
    }

    function setSSL($ssl = true)
    {
        $ssl = $ssl ? '1' : '0';
        return $this->database->queryExec('
            UPDATE channel_servers SET ssl=\'' . $ssl . '\'WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
    }

    function setPath($protocol, $path)
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown protocol: ' .
                $protocol);
        }
        return $this->database->queryExec('
            UPDATE channel_server_' . $protocol . '
            SET path=\'' . sqlite_escape_string($path) . '\'WHERE
              channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
 	          server=\'' . sqlite_escape_string($this->mirror) . '\'');
    }

    function addFunction($type, $version, $name)
    {
        if (!in_array($type, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown protocol: ' .
                $type);
        }
        if (!$this->database->queryExec('
            INSERT INTO channel_server_' . $type . '
             (channel, server, function, version)
             VALUES(\'' . sqlite_escape_string($this->_channel) . '\',
 	                \'' . sqlite_escape_string($this->mirror) . '\',
 	                \'' . sqlite_escape_string($name) . '\',
 	                \'' . sqlite_escape_string($version) . '\'')) {
            $this->database->queryExec('
                UPDATE channel_server_' . $type . '
                SET version=\'' . sqlite_escape_string($version) . '\' WHERE
                    channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
                    function=\'' . sqlite_escape_string($name) . '\' AND
 	                server=\'' . sqlite_escape_string($this->mirror) . '\'');
        }
    }

    function setBaseUrl($resourceType, $url)
    {
        if (!$this->database->queryExec('
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
             VALUES(\'' . sqlite_escape_string($this->_channel) . '\',
 	                \'' . sqlite_escape_string($this->mirror) . '\',
 	                \'' . sqlite_escape_string($resourceType) . '\',
 	                \'' . sqlite_escape_string($url) . '\'')) {
            $this->database->queryExec('
                UPDATE channel_server_rest
                SET baseurl=\'' . sqlite_escape_string($url) . '\' WHERE
                    channel=\'' . sqlite_escape_string($this->_channel) . '\' AND
                    type=\'' . sqlite_escape_string($resourceType) . '\' AND
 	                server=\'' . sqlite_escape_string($this->mirror) . '\'');
        }
    }
}