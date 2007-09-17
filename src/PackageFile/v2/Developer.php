<?php
/**
 * To be used like:
 *
 * <code>
 * $pf->developer['cellog']->name('Greg Beaver')->role('lead')->email('cellog@php.net')->active();
 * echo $pf->developer['cellog']->name;
 * </code>
 */
class PEAR2_Pyrus_PackageFile_v2_Developer implements ArrayAccess
{
    private $_parent;
    private $_developer = null;
    private $_role = null;
    private $_info = array('name' => null, 'user' => null, 'email' => null, 'active' => null);
    function __construct(PEAR2_Pyrus_PackageFile_v2 $parent, $developer = null)
    {
        $this->_parent = $parent;
        if ($developer) {
            $this->_developer = $developer;
            $this->_info['user'] = $developer;
        }
    }

    function __get($var)
    {
        if ($this->_developer === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Cannnot access developer info for unknown developer');
        }
        if (!isset($this->_info[$var])) {
            return null;
        }
        return $this->_info[$var];
    }

    function __call($var, $args)
    {
        if ($this->_developer === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Cannnot set developer info for unknown developer');
        }
        if (!isset($this->_info[$var]) || $var == 'user') {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Cannot set unknown value ' . $var);
        }
        if (count($args) != 1 || !is_string($args[0])) {
            throw new PEAR2_Pyrus_PackageFile_v2_Developer_Exception(
                'Invalid value for ' . $var);
        }
        $this->_info[$var] = $args[0];
        return $this;
    }

    function offsetGet($var)
    {
        return new PEAR2_Pyrus_PackageFile_v2_Developer($this->_parent, $var);
    }

    function offsetSet($var)
    {
        
    }

    function offsetUnset($var)
    {
        // remove developer
    }

    function offsetIsset($var)
    {
        // test whether developer exists
    }
}