<?php
class PEAR2_Pyrus_PackageFile_v2_UsesRoleTask implements ArrayAccess, Iterator, Countable
{
    protected $parent;
    protected $info;
    protected $type;
    protected $index;
    function __construct($parent, array $info, $type, $index = null)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->type = $type;
        $this->index = $index;
    }

    function current()
    {
        $info = current($this->info);
        foreach (array($this->type, 'package', 'channel', 'uri') as $key) {
            if (!array_key_exists($key, $info)) {
                $info[$key] = null;
            }
        }
        return new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask($this, $info, $this->type, key($this->info));
    }

    function count()
    {
        return count($this->info);
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

    function __unset($var)
    {
        if ($this->index === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use [] operator to access ' . $this->type . 's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'Unknown variable ' . $var . ' requested, should be one of ' .
                implode(', ', array_keys($this->info))
            );
        }
        $this->info[$var] = null;
        $this->save();
    }

    function __isset($var)
    {
        if ($this->index === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use [] operator to access ' . $this->type . 's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'Unknown variable ' . $var . ' requested, should be one of ' .
                implode(', ', array_keys($this->info))
            );
        }
        return isset($this->info[$var]);
    }

    function __set($var, $value)
    {
        return $this->__call($var, array($value));
    }

    function __call($var, $args)
    {
        if ($this->index === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use [] operator to access ' . $this->type . 's');
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
        if ($var === 'channel' || $var === 'package') {
            $this->info['uri'] = null;
        } elseif ($var === 'uri') {
            $this->info['channel'] = null;
            $this->info['package'] = null;
        }
        $this->info[$var] = $args[0];
        $this->save();
        return $this;
    }

    function __get($var)
    {
        if ($this->index === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use [] operator to access ' . $this->type . 's');
        }
        if ($var === 'type') {
            return $this->type;
        }
        if (!isset($this->info[$var])) {
            return null;
        }
        return $this->info[$var];
    }

    function locateRoleTask($name)
    {
        if (count($this->info) && !isset($this->info[0])) {
            $this->info = array($this->info);
        }
        foreach ($this->info as $i => $dep)
        {
            $pattern = ($this->type == 'usesrole') ? 'role' : 'task';
            if (isset($dep[$pattern]) && $dep[$pattern] == $name) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        if ($this->index !== null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use -> operator to access properties of a ' . $this->type);
        }
        $i = $this->locateRoleTask($var);
        if (false === $i) {
            $i = count($this->info);
            $info = array(str_replace('uses', '', $this->type) => $var, 'package' => null, 'channel' => null, 'uri' => null);
        } else {
            $info = $this->info[$i];
            foreach (array(str_replace('uses', '', $this->type), 'package', 'channel', 'uri') as $key) {
                if (!array_key_exists($key, $this->info[$i])) {
                    $info[$key] = null;
                }
            }
        }
        return new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask($this, $info, $this->type, $i);
    }

    function offsetSet($var, $value)
    {
        if ($this->index !== null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use -> operator to access properties of a ' . $this->type);
        }
        if (!($value instanceof PEAR2_Pyrus_PackageFile_v2_UsesRoleTask)) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'Can only set ' . $this->type . ' to a PEAR2_Pyrus_PackageFile_v2_UsesRoleTask object');
        }
        if ($value->type != $this->type) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'Cannot set ' . $this->type . ' to a ' . $value->type . ' object');
        }
        $i = $this->locateRoleTask($var);
        if (false === $i) {
            $i = count($this->info);
            $this->info[] = array($this->type => '', 'package' => null, 'channel' => null, 'uri' => null);
        }
        $this->save();
    }

    function offsetExists($var)
    {
        if ($this->index !== null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use -> operator to access properties of a ' . $this->type);
        }
        $i = $this->locateRoleTask($var);
        return $i !== false;
    }

    function offsetUnset($var)
    {
        if ($this->index !== null) {
            throw new PEAR2_Pyrus_PackageFile_v2_UsesRoleTask_Exception(
                'use -> operator to access properties of a ' . $this->type);
        }
        $i = $this->locateRoleTask($var);
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

    function setInfo($type, $info, $index)
    {
        foreach (array_keys($info) as $key) {
            if ($info[$key] === null) {
                unset($info[$key]);
            }
        }
        if (!count($info)) {
            if (isset($this->info[$index])) {
                unset($this->info[$index]);
            }
        } else {
            $this->info[$index] = $info;
        }
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent->setInfo($this->type, $this->info, $this->index);
            $this->parent->save();
            return;
        }
        $info = $this->info;
        if (count($info) == 1) {
            $info = $info[0];
        }
        $this->parent->{'raw' . $this->type} = $info;
    }
}
?>