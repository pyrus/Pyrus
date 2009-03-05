<?php
/**
 * PEAR2_Pyrus_PackageFile_v2_Release_BinaryPackage
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
 * Represents a binarypackage tag in an extsrcrelease or zendextsrcrelease tag
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2_Release_BinaryPackage implements ArrayAccess, Iterator, Countable
{
    protected $info;
    protected $parent;

    /**
     * @param array The content of this element.  All allowed indices should be initialized to null
     */
    function __construct($parent, $info)
    {
        $this->parent = $parent;
        $this->info = array_flip(array_values($info));
    }

    function count()
    {
        return count($this->info);
    }

    function current()
    {
        return key($this->info);
    }

    function next()
    {
        return next($this->info);
    }

    function rewind()
    {
        reset($this->info);
    }

    function valid()
    {
        return key($this->info);
    }

    function key()
    {
        return key($this->info);
    }

    function offsetUnset($var)
    {
        unset($this->info[$var]);
        $this->save();
    }

    function offsetGet($var)
    {
        if (!isset($this->info[$var])) {
            return null;
        }
        return $var;
    }

    function offsetSet($var, $value)
    {
        if (!is_string($value)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Can only set binarypackage to string');
        }
        $this->info[$value] = 1;
        $this->save();
    }

    function offsetExists($var)
    {
        return isset($this->info[$var]);
    }

    function getInfo()
    {
        $ret = array_keys($this->info);
        if (count($ret) == 1) {
            return $ret[0];
        }
        return $ret;
    }

    function save()
    {
        $info = array_keys($this->info);
        $this->parent->setBinaryPackage($this->getInfo());
        $this->parent->save();
    }
}