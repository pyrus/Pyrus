<?php
/**
 * \Pyrus\PackageFile\v2\BundledPackage
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

namespace Pyrus\PackageFile\v2;

/**
 * Represents bundled packages in a package.xml bundle package type
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class BundledPackage implements \ArrayAccess, \Countable, \Iterator
{
    protected $info;
    protected $parent;

    /**
     * @param array The content of this element.  All allowed indices should be initialized to null
     */
    function __construct($parent, $info)
    {
        $this->parent = $parent;
        if (is_string($info)) {
            $info = array($info);
        }
        $this->info = $info;
    }

    function count()
    {
        return count($this->info);
    }

    function current()
    {
        return current($this->info);
    }

    function next()
    {
        next($this->info);
    }

    function key()
    {
        return current($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function rewind()
    {
        reset($this->info);
    }

    function locatePackage($package)
    {
        foreach ($this->info as $i => $bundle) {
            if ($bundle === $package) {
                return $i;
            }
        }

        return false;
    }

    function offsetUnset($var)
    {
        $i = $this->locatePackage($var);
        if ($i === false) {
            return;
        }

        unset($this->info[$i]);
        $this->save();
    }

    function offsetGet($var)
    {
        $i = $this->locatePackage($var);
        if ($i === false) {
            return false;
        }

        return $var;
    }

    function offsetSet($var, $value)
    {
        if (!is_string($value)) {
            throw new \Pyrus\PackageFile\Exception('Can only set bundledpackage to string');
        }

        // $var is ignored
        $i = $this->locatePackage($value);
        if ($i === false) {
            $i = count($this->info);
        }

        $this->info[$i] = $value;
        $this->save();
    }

    function offsetExists($var)
    {
        $i = $this->locatePackage($var);
        return $i !== false;
    }

    function getInfo()
    {
        return $this->info;
    }

    function save()
    {
        $info = $this->info;
        if (count($info) == 1) {
            $info = $info[0];
        }

        $this->parent->rawcontents = array('bundledpackage' => $info);
    }
}