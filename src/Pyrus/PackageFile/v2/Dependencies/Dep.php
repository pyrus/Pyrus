<?php
class PEAR2_Pyrus_PackageFile_v2_Dependencies_Dep implements ArrayAccess, Iterator
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
        return current($this->info);
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
        if ($this->type == 'os' || $this->type == 'arch') {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Unknown method ' . $var . ' called');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Unknown variable ' . $var . '(), must be one of ' .
                            implode(', ', array_keys($this->info)));
        }
        if ($args[0] === null) {
            $this->info[$var] = null;
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

    function __get($var)
    {
        if (!isset($this->info[$var])) {
            return null;
        }
        if ($var == 'exclude') {
            $ret = $this->info['exclude'];
            if (!is_array($ret)) {
                return $ret;
            }
        }
        return $this->info[$var];
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
        if (count($info) == 1) {
            $info = $info[0];
        }
        $this->parent->setInfo($this->type, $info);
        $this->parent->save();
    }
}
?>