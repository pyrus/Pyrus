<?php
class PEAR2_Pyrus_Registry_Sqlite_Channel extends PEAR2_Pyrus_Registry_Sqlite
    implements ArrayAccess, PEAR2_Pyrus_IChannel
{
    private $_channelname;
    private $_mirror;
    function __construct(SQLiteDatabase $database)
    {
        $this->_database = $database;
    }

 	function getName()
 	{
 	    return $this->_channelName;
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
 	    if (!isset($this->_channelname)) {
 	        throw new PEAR2_Pyrus_Registry_Exception('Action requested for unknown channel');
 	    }
 	    switch ($value) {
 	        case 'mirror' :
 	            $a = new PEAR2_Pyrus_Registry_Sqlite_Channel_Mirror($this, $this->_channelname);
 	            return $a;
 	        case 'mirrors' :
 	            $a = new PEAR2_Pyrus_Registry_Sqlite_Channel_Mirrors($this, $this->_channelname);
 	            return $a;
 	    }
 	}

 	public function toChannelObject()
 	{
 	    $a = new PEAR2_Pyrus_Channel;
 	    $a->setName($this->getName());
 	    $a->setSummary($this->getSummary());
 	    $a->setPort($this->getPort());
 	}

 	public function __toString()
 	{
 	    return $this->toChannelObject()->__toString();
 	}
}