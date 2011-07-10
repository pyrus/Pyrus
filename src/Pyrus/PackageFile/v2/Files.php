<?php
/**
 * \Pyrus\PackageFile\v2\Files
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Represents the files within a package file
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\PackageFile\v2;
class Files implements \ArrayAccess, \Iterator
{
    protected $info;
    protected $parent;

    function __construct($parent, $info)
    {
        $this->parent = $parent;
        $this->info = $info;
    }

    function offsetUnset($var)
    {
        unset($this->info[$var]);
        $this->parent->setFilelistFile($var, null);
    }

    function offsetGet($var)
    {
        if (isset($this->info[$var])) {
            return new Files\File($this, $this->parent, $this->info[$var]);
        }

        return null;
    }

    function offsetSet($var, $value)
    {
        if ($value instanceof \ArrayObject) {
            $value = $value->getArrayCopy();
        }

        if (!is_array($value)) {
            throw new Files\Exception('File must be an array of attributes and tasks');
        }

        if (!isset($value['attribs'])) {
            // no tasks is assumed
            $value = array('attribs' => $value);
        }

        $value['attribs']['name'] = $var;
        if (!isset($value['attribs']['role'])) {
            throw new Files\Exception('File role must be set for file ' . $var);
        }

        $this->info[$var] = $value;
        $this->parent->setFilelistFile($var, $value);
    }

    function offsetExists($var)
    {
        return isset($this->info[$var]);
    }

    function valid()
    {
        return $this->key();
    }

    function next()
    {
        return next($this->info);
    }

    function current()
    {
        return $this[$this->key()];
    }

    function key()
    {
        return key($this->info);
    }

    function rewind()
    {
        reset($this->info);
    }
}