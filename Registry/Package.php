<?php
class PEAR2_Pyrus_Registry_Package implements ArrayAccess
{
    private $_packagename;
    private $_reg;
    function __construct(PEAR2_Pyrus_IRegistry $cloner)
    {
        $this->_reg = $cloner;
    }

    function offsetExists($offset)
    {
 	    $info = $this->_reg->parsePackageName($offset);
        return $this->_reg->packageExists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
 	{
 	    $this->_packagename = $this->_reg->parsePackageName($this->_packagename);
 	    $ret = clone $this;
 	    return $ret;
 	}
 	
 	function offsetSet($offset, $value)
 	{
 	    if ($offset == 'upgrade') {
 	        $this->_reg->upgradePackage($value);
 	    }
 	    if ($offset == 'install') {
 	        $this->_reg->installPackage($value);
 	    }
 	}

 	function offsetUnset($offset)
 	{
 	    $info = $this->parsePackageName($offset);
 	    $this->uninstallPackage($info['package'], $info['channel']);
 	}

 	function __get($var)
 	{
 	    if (!isset($this->_packagename)) {
 	        throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
 	    }
 	    return $this->_reg->packageInfo($this->_packagename['package'], 
 	      $this->_packagename['channel'], $var);
 	}

 	function __call($method, $args)
 	{
 	    return call_user_func_array(array($this->_reg, $method), $args);
 	}
}