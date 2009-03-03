<?php
/**
 * PEAR2_Pyrus_PackageFile_v2_Files
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
 * Represents the files within a package file
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2_Files implements ArrayAccess
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
            return $this->info[$var];
        }
        return null;
    }

    function offsetSet($var, $value)
    {
        if ($value instanceof ArrayObject) {
            $value = $value->getArrayCopy();
        }
        if (!is_array($value)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Files_Exception('File must be an array of '
                . 'attributes and tasks');
        }

        if (!isset($value['attribs'])) {
            // no tasks is assumed
            $value = array('attribs' => $value);
        }

        $value['attribs']['name'] = $var;
        if (!isset($value['attribs']['role'])) {
            throw new PEAR2_Pyrus_PackageFile_v2_Files_Exception('File role must be set for' .
                ' file ' . $var);
        }

        $this->info[$var] = $value;
        $this->parent->setFilelistFile($var, $value);
    }

    function offsetExists($var)
    {
        return isset($this->info[$var]);
    }
}