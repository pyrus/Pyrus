<?php
class PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite implements PEAR2_Pyrus_IChannel
{
    /**
     * The database resource
     *
     * @var SQLiteDatabase
     */
    protected $database;
    private $_path;
    private $_channelname;
    private $_mirror;

    function __construct(SQLiteDatabase $db, $channel)
    {
        $channel = strtolower($channel);
        $this->database = $db;
        $this->_channelname = $channel;
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

 	function getPort($mirror = false)
 	{
 	    return $this->database->singleQuery('SELECT port FROM channel_servers WHERE
 	          channel=\'' . sqlite_escape_string($this->_channelname) . '\' AND
 	          server=\'' . sqlite_escape_string($this->_channelname) . '\'');
 	}

 	function getSSL($mirror = false)
 	{
 	    return $this->database->singleQuery('SELECT ssl FROM channel_servers WHERE
 	          channel=\'' . sqlite_escape_string($this->_channelname) . '\' AND
 	          server=\'' . sqlite_escape_string($this->_channelname) . '\'');
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

 	function getValidationObject($package)
 	{
 	    $a = $this->getValidatePackage($package);
 	    return new $a;
 	}

 	function __get($value)
 	{
 	    switch ($value) {
 	        case 'mirror' :
 	            $a = new PEAR2_Pyrus_ChannelRegistry_Mirror_Sqlite($this, $this->_channelname);
 	            return $a;
 	        case 'mirrors' :
 	            $ret = array();
 	            foreach ($this->database->arrayQuery('SELECT server FROM channel_servers
 	                  WHERE channel=\'' . sqlite_escape_string($this->_channelname) . '\'
 	                  AND server !=\'' . sqlite_escape_string($this->_channelname) . '\'') as $mirror) {
 	                $ret[$mirror] = new PEAR2_Pyrus_ChannelRegistry_Mirror_Sqlite($this,
 	                    $this->_channelname, $mirror);
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
 	    $a->setValidationPackage()
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
        
    }

    public function supports($type, $name = null, $version = '1.0')
    {
        
    }
}