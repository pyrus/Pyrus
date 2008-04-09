<?php
/**
 * PEAR2_Pyrus_Registry_Sqlite_Package
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
 * Package within the sqlite registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Sqlite_Package extends PEAR2_Pyrus_Registry_Sqlite implements ArrayAccess
{
    private $_packagename;
    function __construct(PEAR2_Pyrus_Registry_Sqlite $cloner)
    {
        parent::__construct($cloner->getDatabase());
    }

    function offsetExists($offset)
    {
 	    $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
        return $this->exists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
 	{
 	    $this->_packagename = $offset;
 	    $ret = clone $this;
 	    unset($this->_packagename);
 	    return $ret;
 	}
 	
 	function offsetSet($offset, $value)
 	{
 	    if ($offset == 'install') {
 	        $this->install($value);
 	    }
 	}

 	function offsetUnset($offset)
 	{
 	    $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
 	    $this->uninstall($info['package'], $info['channel']);
 	}

 	function __get($var)
 	{
 	    if (!isset($this->_packagename)) {
 	        throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
 	    }
 	    $info =  PEAR2_Pyrus_Config::current()->channelregistry->parseName($this->_packagename);
 	    return $this->info($info['package'], $info['channel'], $var);
 	}
}