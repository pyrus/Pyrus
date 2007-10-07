<?php
abstract class PEAR2_Pyrus_Registry_Base implements ArrayAccess, PEAR2_Pyrus_IRegistry, Iterator
{
    protected $packagename;
    protected $packageList = array();
    function offsetExists($offset)
    {
 	    $info = PEAR2_Pyrus_ChannelRegistry::parsePackageName($offset);
 	    if (is_string($info)) {
 	        return false;
 	    }
        return $this->exists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
 	{
 	    $info = PEAR2_Pyrus_ChannelRegistry::parsePackageName($offset, true);
 	    $this->packagename = $offset;
 	    $ret = clone $this;
 	    unset($this->packagename);
 	    return $ret;
 	}
 	
 	function offsetSet($offset, $value)
 	{
 	    if ($offset == 'upgrade') {
 	        $this->upgrade($value);
 	    }
 	    if ($offset == 'install') {
 	        $this->install($value);
 	    }
 	}

 	function offsetUnset($offset)
 	{
 	    $info = PEAR2_Pyrus_ChannelRegistry::parsePackageName($offset);
 	    if (is_string($info)) {
 	        return;
 	    }
 	    $this->uninstall($info['package'], $info['channel']);
 	}

 	function __get($var)
 	{
 	    if (!isset($this->packagename)) {
 	        throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
 	    }
 	    $info = $this->parsePackageName($this->_packagename);
 	    return $this->info($info['package'], $info['channel'], $var);
 	}

    function current()
    {
        $packagename = current($this->packageList);
        return $this->package[PEAR2_Pyrus_Config::current()->default_channel . '/' . $packagename];
    }

 	function key()
 	{
 	    return key($this->packageList);
 	}

 	function valid()
 	{
 	    return current($this->packageList);
 	}

 	function next()
 	{
 	    return next($this->packageList);
 	}

 	function rewind()
 	{
 	    $this->packageList = $this->listPackages(PEAR2_Pyrus_Config::current()->default_channel);
 	}
}