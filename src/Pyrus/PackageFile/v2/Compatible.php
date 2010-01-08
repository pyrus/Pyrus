<?php
/**
 * Manage compatible packages with this one
 *
 * To be used like:
 * <code>
 * // add a new compatible declaration or replace an existing one
 * $pf->compatible['pear.php.net/Archive_Tar']
 *         ->min('1.2')
 *         ->max('1.3.0')
 *         ->exclude('1.2.1', '1.2.2');
 * // remove a compatibility declaration
 * unset($pf->compatible['pear.php.net/Archive_Tar']);
 * // test for existence of compatible declaration
 * isset($pf->compatible['pear.php.net/Archive_Tar']);
 * // display info:
 * echo $pf->compatible['pear.php.net/Archive_Tar']->min;
 * foreach ($pf->compatible as $package => $info) {
 *     echo $info->min;
 *     echo $info->max;
 *     if (isset($info->exclude)) {
 *         foreach ($info->exclude as $version) {
 *             echo $version;
 *         }
 *     }
 * }
 * </code>
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\v2;
class Compatible implements \ArrayAccess, \Iterator, \Countable
{
    protected $info;
    protected $index = null;
    protected $parent;

    function __construct($parent, array $info, $index = null)
    {
        $this->parent = $parent;
        if (!($parent instanceof self) && !isset($info[0]) && count($info)) {
            $info = array($info);
        }
        $this->info = $info;
        $this->index = $index;
    }

    function count()
    {
        return count($this->info);
    }

    function current()
    {
        $i = key($this->info);
        return new \pear2\Pyrus\PackageFile\v2\Compatible($this, $this->info[$i], $i);
    }

    function rewind()
    {
        reset($this->info);
    }

    function key()
    {
        $i = key($this->info);
        return $this->info[$i]['channel'] . '/' . $this->info[$i]['name'];
    }

    function next()
    {
        return next($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function locateCompatible($name)
    {
        $stuff = explode('/', $name);
        $name = array_pop($stuff);
        $channel = implode('/', $stuff);
        foreach ($this->info as $i => $compat)
        {
            if (isset($compat['name']) && $compat['name'] == $name
                && isset($compat['channel']) && $compat['channel'] == $channel) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Use -> operator to access compatible package properties');
        }
        $i = $this->locateCompatible($var);
        if (false === $i) {
            $i = count($this->info);
            if (!strpos($var, '/')) {
                throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Cannot access "' . $var .
                    '", must use "channel/package" to specify a compatible package to access');
            }
            $stuff = explode('/', $var);
            $name = array_pop($stuff);
            $channel = implode('/', $stuff);
            $this->info[$i] = array('name' => $name, 'channel' => $channel,
                                        'min' => null, 'max' => null,
                                        'exclude' => null);
        } else {
            foreach (array('name', 'channel', 'min', 'max', 'exclude') as $key) {
                if (!array_key_exists($key, $this->info[$i])) {
                    $this->info[$i][$key] = null;
                }
            }
        }
        return new \pear2\Pyrus\PackageFile\v2\Compatible($this, $this->info[$i], $i);
    }

    function offsetSet($var, $value)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Use -> operator to access compatible package properties');
        }
        if (!($value instanceof self)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Can only set $pf->compatible[\'' .
                $var . '\'] to \pear2\Pyrus\PackageFile\v2\Compatible object');
        }
        if ($var === null) {
            $var = $value->channel . '/' . $value->name;
        }
        if (!strpos($var, '/')) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Cannot set "' . $var .
                '", must use "channel/package" to specify a compatible package to set');
        }
        $stuff = explode('/', $var);
        $name = array_pop($stuff);
        $channel = implode('/', $stuff);
        if ($value->name != $name || $value->channel != $channel) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Cannot set ' .
                $channel . '/' . $name . ' to ' .
                $value->channel . '/' . $value->name .
                ', use $pf->compatible[] to set a new value');
        }
        if (false === ($i = $this->locateCompatible($var))) {
            $i = count($this->info);
        }
        $this->info[$i] = $value->getInfo();
        $this->save();
    }

    function offsetExists($var)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Use -> operator to access compatible package properties');
        }
        $i = $this->locateCompatible($var);
        return $i !== false;
    }

    function offsetUnset($var)
    {
        if (isset($this->index)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Use -> operator to access compatible package properties');
        }
        $i = $this->locateCompatible($var);
        if ($i === false) {
            return;
        }
        unset($this->info[$i]);
        $this->info = array_values($this->info);
        $this->save();
    }

    function __isset($var)
    {
        if (!isset($this->index)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Use [] operator to access compatible packages');
        }
        if (!isset($this->info[$var])) {
            return null;
        }
        return isset($this->info[$var]);
    }

    function __get($var)
    {
        if (!isset($this->index)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Use [] operator to access compatible packages');
        }
        if (!isset($this->info[$var])) {
            return null;
        }
        if ($var === 'exclude') {
            $ret = $this->info['exclude'];
            if (!is_array($ret)) {
                return array($ret);
            }
        }
        return $this->info[$var];
    }

    function __set($var, $value)
    {
        return $this->__call($var, array($value));
    }

    function __call($var, $args)
    {
        if (!isset($this->index)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception('Use [] operator to access compatible packages');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', array_keys($this->info))
            );
        }
        if ($var === 'name' || $var === 'channel') {
            throw new \pear2\Pyrus\PackageFile\v2\Compatible\Exception(
                'Cannot change compatible package name, use unset() to remove the old compatible package'
            );
        }
        if (!count($args) || $args[0] === null) {
            unset($this->info[$var]);
            $this->save();
            return $this;
        }
        if ($var == 'exclude') {
            if (!isset($this->info[$var])) {
                $this->info[$var] = $args;
            } else {
                $this->info[$var] = array_merge($this->info[$var], $args);
            }
        } else {
            $this->info[$var] = $args[0];
        }
        $this->save();
        return $this;
    }

    function getInfo()
    {
        return $this->info;
    }

    function setInfo($index, $info)
    {
        foreach ($info as $key => $null) {
            if ($null === null) {
                unset($info[$key]);
            }
        }
        $this->info[$index] = $info;
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent->setInfo($this->index, $this->info);
            $this->parent->save();
        } else {
            $info = $this->info;
            if (count($info) == 1) {
                $info = $info[0];
            }
            $this->parent->rawcompatible = $info;
        }
    }
}