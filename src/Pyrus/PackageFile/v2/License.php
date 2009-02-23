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

    function getArray()
    {
        $arr = $this->array;
        return $arr;
    }

    function offsetUnset($var)
    {
        if ($var == 'name') {
            if (is_array($this->array) && isset($this->array['_content'])) {
                unset($this->array['_content']);
            } elseif (is_string($this->array)) {
                $this->array = array();
            }
            return;
        }
        if ($var == 'uri') {
            unset($this->array['attribs']['uri']);
            if (!count($this->array['attribs'])) {
                unset($this->array['attribs']);
                if (isset($this->array['_content'])) {
                    $this->array = $this->array['_content'];
                }
            }
            return;
        }
        if ($var == 'path') {
            unset($this->array['attribs']['path']);
            if (!count($this->array['attribs'])) {
                unset($this->array['attribs']);
                if (isset($this->array['_content'])) {
                    $this->array = $this->array['_content'];
                }
            }
            return;
        }
    }

    function offsetGet($var)
    {
        if ($var == 'uri') {
            if (is_array($this->array) && isset($this->array['attribs']) && isset($this->array['attribs']['uri'])) {
                return $this->array['attribs']['uri'];
            }
            return null;
        }
        if ($var == 'path') {
            if (is_array($this->array) && isset($this->array['attribs']) && isset($this->array['attribs']['path'])) {
                return $this->array['attribs']['path'];
            }
            return null;
        }
        if ($var == 'name') {
            if (is_array($this->array) && isset($this->array['_content'])) {
                return $this->array['_content']; 
            }
            if (!is_array($this->array)) {
                return $this->array;
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
            if (!is_array($this->array) || !isset($this->array['attribs'])) {
                if (!is_array($this->array)) {
                    $this->array = array('_content' => $this->array);
                }
                $this->array['attribs'] = array();
            }
        } else {
            if ($var == 'name') {
                if (!is_array($this->array)) {
                    $this->array = $value;
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
            if (!is_array($this->array)) {
                return sizeof($this->array);
            }
            return isset($this->array['_content']);
        }
        return false;
    }
}