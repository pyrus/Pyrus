<?php
/**
 * PEAR2_Pyrus_PackageFile_v2_License
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
class PEAR2_Pyrus_PackageFile_v2_License implements ArrayAccess
{
    protected $array;
    function __construct(&$array)
    {
        $this->array = &$array;
    }

    function link(&$array)
    {
        $null = 1;
        $this->array = &$null;
        $this->array = &$array;
    }

    function getArray()
    {
        $arr = $this->array;
        return $arr;
    }
    function offsetUnset($var)
    {
        if ($var == 'name') {
            if (isset($this->array['_content'])) {
                unset($this->array['_content']);
            } elseif (isset($this->array[0])) {
                unset($this->array[0]);
            }
            return;
        }
        if ($var == 'uri') {
            unset($this->array['attribs']['uri']);
            if (!count($this->array['attribs'])) {
                unset($this->array['attribs']);
                if (isset($this->array['_content'])) {
                    $this->array[0] = $this->array['_content'];
                    unset($this->array['_content']);
                }
            }
            return;
        }
        if ($var == 'path') {
            unset($this->array['attribs']['path']);
            if (!count($this->array['attribs'])) {
                unset($this->array['attribs']);
                if (isset($this->array['_content'])) {
                    $this->array[0] = $this->array['_content'];
                    unset($this->array['_content']);
                }
            }
            return;
        }
    }

    function offsetGet($var)
    {
        if ($var == 'uri') {
            if (isset($this->array['attribs']) && isset($this->array['attribs']['uri'])) {
                return $this->array['attribs']['uri'];
            }
            return null;
        }
        if ($var == 'path') {
            if (isset($this->array['attribs']) && isset($this->array['attribs']['path'])) {
                return $this->array['attribs']['path'];
            }
            return null;
        }
        if ($var == 'name') {
            if (isset($this->array['_content'])) {
                return $this->array['_content']; 
            }
            if (isset($this->array[0])) {
                return $this->array[0];
            }
        }
        return null;
    }

    function offsetSet($var, $value)
    {
        if (!is_string($value)) {
            throw new PEAR2_Pyrus_PackageFile_v2_License_Exception('Can only set license to string');
        }

        if ($var == 'path' || $var == 'uri') {
            if (!isset($this->array['attribs'])) {
                if (isset($this->array[0])) {
                    $this->array['_content'] = $this->array[0];
                    unset($this->array[0]);
                }
                $this->array['attribs'] = array();
            }
        } else {
            if ($var == 'name') {
                if (!isset($this->array['attribs'])) {
                    $this->array[0] = $value;
                    return;
                }
                $this->array['_content'] = $value;
                return;
            }
            throw new PEAR2_Pyrus_PackageFile_v2_License_Exception('Unknown license trait ' . $var . ', cannot set value');
        }
        $this->array['attribs'][$var] = $value;
    }

    function offsetExists($var)
    {
        if ($var == 'uri') {
            return isset($this->array['attribs']) && isset($this->array['attribs']['uri']);
        }
        if ($var == 'path') {
            return isset($this->array['attribs']) && isset($this->array['attribs']['path']);
        }
        if ($var == 'name') {
            return (isset($this->array['attribs']) && isset($this->array['_content'])) ||
                    isset($this->array[0]);
        }
        return false;
    }
}