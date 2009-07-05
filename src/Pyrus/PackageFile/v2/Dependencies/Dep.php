<?php
namespace pear2\Pyrus\PackageFile\v2\Dependencies;
class Dep implements \ArrayAccess, \Iterator
{
    protected $parent;
    protected $info;
    protected $type;
    function __construct($parent, array $info, $type)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->type = $type;
    }

    function current()
    {
        return new self($this, current($this->info), $this->type);
    }

    function rewind()
    {
        if (count($this->info) && !isset($this->info[0])) {
            $this->info = array($this->info);
        }
        reset($this->info);
    }

    function key()
    {
        return key($this->info);
    }

    function next()
    {
        return next($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function __call($var, $args)
    {
        if (!array_key_exists($var, $this->info)) {
            if ($this->type == 'os') {
                if ($var == 'name' || $var == 'conflicts') {
                    goto set_ok;
                }
                $keys = array('name', 'conflicts');
            } elseif ($this->type == 'arch') {
                if ($var == 'pattern' || $var == 'conflicts') {
                    goto set_ok;
                }
                $keys = array('pattern', 'conflicts');
            } else {
                $keys = array_keys($this->info);
            }
            throw new \pear2\Pyrus\PackageFile\v2\Dependencies\Exception('Unknown variable ' . $var . ', must be one of ' .
                            implode(', ', $keys));
        }
set_ok:
        if ($args[0] === null) {
            $this->info[$var] = null;
            $this->save();
            return $this;
        }
        if ($var == 'exclude') {
            if (!isset($this->info[$var])) {
                $this->info[$var] = $args;
            } else {
                if (!is_array($this->info[$var])) {
                    $this->info[$var] = array($this->info[$var]);
                }
                $this->info[$var] = array_merge($this->info[$var], $args);
            }
        } elseif ($var == 'conflicts') {
            if ($args[0]) {
                $this->info[$var] = '';
            } else {
                $this->info[$var] = null;
            }
        } else {
            $this->info[$var] = $args[0];
        }
        $this->save();
        return $this;
    }

    function __get($var)
    {
        if (!isset($this->info[$var])) {
            return null;
        }
        if ($var == 'exclude') {
            $ret = $this->info['exclude'];
            if (!is_array($ret)) {
                return array($ret);
            }
        } elseif ($var == 'conflicts') {
            if (isset($this->info['conflicts'])) {
                return true;
            }
            return false;
        }
        return $this->info[$var];
    }

    function __set($var, $value)
    {
        $this->__call($var, array($value));
    }

    function __isset($var)
    {
        return isset($this->info[$var]);
    }

    function locateDep($name)
    {
        if (count($this->info) && !isset($this->info[0])) {
            $this->info = array($this->info);
        }
        foreach ($this->info as $i => $dep)
        {
            $pattern = ($this->type == 'os') ? 'name' : 'pattern';
            if (isset($dep[$pattern]) && $dep[$pattern] == $name) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        $i = $this->locateDep($var);
        if (false === $i) {
            return null;
        }
        return !isset($this->info[$i]['conflicts']);
    }

    function offsetSet($var, $value)
    {
        $i = $this->locateDep($var);
        if (false === $i) {
            $i = count($this->info);
            $pattern = ($this->type == 'os') ? 'name' : 'pattern';
            $this->info[] = array($pattern => $var);
        }
        if ($value) {
            if (isset($this->info[$i]['conflicts'])) {
                unset($this->info[$i]['conflicts']);
            }
        } else {
            $this->info[$i]['conflicts'] = '';
        }
        $this->save();
    }

    function offsetExists($var)
    {
        return false !== $this->locateDep($var);
    }

    function offsetUnset($var)
    {
        $i = $this->locateDep($var);
        if ($i === false) {
            return;
        }
        unset($this->info[$i]);
        $this->info = array_values($this->info);
        $this->save();
    }

    function getInfo()
    {
        return $this->info;
    }

    function save()
    {
        $info = $this->info;
        if ($this->type === 'php' || $this->type === 'pearinstaller') {
            foreach ($info as $key => $val) {
                if ($key === 'exclude' && is_array($val) && count($val) == 1) {
                    $info[$key] = $val[0];
                }
            }
        } else {
            if (is_array($info) && count($info) == 1 && isset($info[0])) {
                $info = $info[0];
            }
        }
        $this->parent->setInfo($this->type, $info);
        $this->parent->save();
        return $this;
    }
}
?>