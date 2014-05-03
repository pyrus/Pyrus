<?php
/**
 * \Pyrus\PackageFile\v2\License
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
 * Represents the files within a package file
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class License implements \ArrayAccess
{
    protected $parent;
    protected $info;

    function __construct($parent, $info)
    {
        $this->parent = $parent;
        $this->info = $info;
    }

    function getInfo()
    {
        return $this->info;
    }

    function offsetUnset($var)
    {
        if ($var == 'name') {
            if (is_array($this->info) && isset($this->info['_content'])) {
                unset($this->info['_content']);
            } elseif (is_string($this->info)) {
                $this->info = array();
            }

            $this->save();
            return;
        }

        if ($var == 'uri') {
            unset($this->info['attribs']['uri']);
            if (!count($this->info['attribs'])) {
                unset($this->info['attribs']);
                if (isset($this->info['_content'])) {
                    $this->info = $this->info['_content'];
                }
            }

            $this->save();
            return;
        }

        if ($var == 'filesource') {
            unset($this->info['attribs']['filesource']);
            if (!count($this->info['attribs'])) {
                unset($this->info['attribs']);
                if (isset($this->info['_content'])) {
                    $this->info = $this->info['_content'];
                }
            }

            $this->save();
            return;
        }
    }

    function offsetGet($var)
    {
        if ($var == 'uri') {
            if (is_array($this->info) && isset($this->info['attribs']) && isset($this->info['attribs']['uri'])) {
                return $this->info['attribs']['uri'];
            }

            return null;
        }

        if ($var == 'filesource') {
            if (is_array($this->info) && isset($this->info['attribs']) && isset($this->info['attribs']['filesource'])) {
                return $this->info['attribs']['filesource'];
            }

            return null;
        }

        if ($var == 'name') {
            if (is_array($this->info) && isset($this->info['_content'])) {
                return $this->info['_content'];
            }

            if (!is_array($this->info)) {
                return $this->info;
            }
        }

        return null;
    }

    function offsetSet($var, $value)
    {
        if (!is_string($value)) {
            throw new License\Exception('Can only set license to string');
        }

        if ($var == 'filesource' || $var == 'uri') {
            if (!is_array($this->info) || !isset($this->info['attribs'])) {
                if (!is_array($this->info)) {
                    $this->info = array('_content' => $this->info);
                }

                $this->info['attribs'] = array();
            }
        } else {
            if ($var == 'name') {
                if (!is_array($this->info)) {
                    $this->info = $value;
                    $this->save();
                    return;
                }

                $this->info['_content'] = $value;
                $this->save();
                return;
            }

            throw new License\Exception('Unknown license trait ' . $var . ', cannot set value');
        }

        $this->info['attribs'][$var] = $value;
        $this->save();
    }

    function offsetExists($var)
    {
        if ($var == 'uri') {
            return isset($this->info['attribs']) && isset($this->info['attribs']['uri']);
        }

        if ($var == 'filesource') {
            return isset($this->info['attribs']) && isset($this->info['attribs']['filesource']);
        }

        if ($var == 'name') {
            if (!is_array($this->info)) {
                return sizeof($this->info);
            }

            return isset($this->info['_content']);
        }

        return false;
    }

    function save()
    {
        $this->parent->rawlicense = $this->info;
    }
}