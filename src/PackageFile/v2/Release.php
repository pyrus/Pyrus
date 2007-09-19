<?php
/**
 * Manage a release in package.xml
 * 
 * To be used like:
 *
 * <code>
 * </code>
 */
class PEAR2_Pyrus_PackageFile_v2_Release implements ArrayAccess
{
    private $_packageInfo;
    function __construct(array &$parent)
    {
        $this->_packageInfo = &$parent;
    }

    function __get($var)
    {
    }

    function __call($var, $args)
    {
    }

    function offsetGet($var)
    {
    }

    function offsetSet($var, $value)
    {
    }

    /**
     * @param string $var
     */
    function offsetUnset($var)
    {
    }

    /**
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
    }
}