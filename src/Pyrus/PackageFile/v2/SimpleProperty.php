<?php
/**
 * \pear2\Pyrus\PackageFile\v2\SImpleProperty
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Represents a simple array property like version or stability
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\v2;
class SimpleProperty implements \ArrayAccess
{
    protected $info;
    protected $name;
    protected $parent;

    /**
     * @param array The content of this element.  All allowed indices should be initialized to null
     */
    function __construct($parent, $info, $name)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->name = $name;
    }

    function offsetUnset($var)
    {
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\Exception('Unknown ' . $this->name . ' property ' . $var);
        }
        $this->info[$var] = null;
        $this->save();
    }

    function offsetGet($var)
    {
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\Exception('Unknown ' . $this->name . ' property ' . $var);
        }
        return $this->info[$var];
    }

    function offsetSet($var, $value)
    {
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\Exception('Unknown ' . $this->name . ' property ' . $var);
        }
        if (!is_string($value)) {
            throw new \pear2\Pyrus\PackageFile\Exception('Can only set ' . $this->name . ' to string');
        }
        $this->info[$var] = $value;
        $this->save();
    }

    function offsetExists($var)
    {
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\Exception('Unknown ' . $this->name . ' property ' . $var);
        }
        return isset($this->info[$var]);
    }

    function getInfo()
    {
        return $this->info;
    }

    function save()
    {
        $info = $this->info;
        foreach(array_keys($this->info) as $key) {
            if (null === $info[$key]) {
                unset($info[$key]);
            }
        }
        $this->parent->{'raw' . $this->name} = $info;
    }
}