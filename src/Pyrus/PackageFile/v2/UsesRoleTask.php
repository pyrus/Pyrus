<?php
namespace pear2\Pyrus\PackageFile\v2;
class UsesRoleTask implements \ArrayAccess, \Iterator, \Countable
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
        return new \pear2\Pyrus\PackageFile\v2\UsesRoleTask($this, $info, $this->type, key($this->info));
    }

    function count()
    {
        return count($this->info);
    }

    function rewind()
    {
        reset($this->info);
    }

    function key()
    {
        $info = current($this->info);
        return $info[$this->type];
    }

    function next()
    {
        return next($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function locateRoleTask($name)
    {
        foreach ($this->info as $i => $dep)
        {
            if (isset($dep[$this->type]) && $dep[$this->type] == $name) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use -> operator to access properties of a uses' . $this->type);
        }
        $i = $this->locateRoleTask($var);
        if (false === $i) {
            $i = count($this->info);
            $info = array($this->type => $var, 'package' => null, 'channel' => null, 'uri' => null);
        } else {
            $info = $this->info[$i];
            foreach (array($this->type, 'package', 'channel', 'uri') as $key) {
                if (!array_key_exists($key, $this->info[$i])) {
                    $info[$key] = null;
                }
            }
        }
        return new \pear2\Pyrus\PackageFile\v2\UsesRoleTask($this, $info, $this->type, $i);
    }

    function offsetSet($var, $value)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use -> operator to access properties of a uses' . $this->type);
        }
        if (!($value instanceof \pear2\Pyrus\PackageFile\v2\UsesRoleTask)) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'Can only set uses' . $this->type . ' to a \pear2\Pyrus\PackageFile\v2\UsesRoleTask object');
        }
        if ($value->type != $this->type) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'Cannot set uses' . $this->type . ' to a uses' . $value->type . ' object');
        }
        $i = $this->locateRoleTask($var);
        if (false === $i) {
            $i = count($this->info);
        }
        $this->info[$i] = array($this->type => $var,
                                'package' => $value->package,
                                'channel' => $value->channel,
                                'uri' => $value->uri);
        $this->save();
    }

    function offsetExists($var)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use -> operator to access properties of a uses' . $this->type);
        }
        $i = $this->locateRoleTask($var);
        return $i !== false;
    }

    function offsetUnset($var)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use -> operator to access properties of a uses' . $this->type);
        }
        $i = $this->locateRoleTask($var);
        if ($i === false) {
            return;
        }
        unset($this->info[$i]);
        $this->info = array_values($this->info);
        $this->save();
    }

    function __unset($var)
    {
        if ($this->index === null) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use [] operator to access uses' . $this->type . 's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
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
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use [] operator to access uses' . $this->type . 's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
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
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use [] operator to access uses' . $this->type . 's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception('Unknown variable ' . $var . ', must be one of ' .
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
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception(
                'use [] operator to access uses' . $this->type . 's');
        }
        if ($var === 'type') {
            return 'uses' . $this->type;
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\UsesRoleTask\Exception('Unknown variable ' . $var . ', must be one of ' .
                            implode(', ', array_keys($this->info)));
        }
        if (!isset($this->info[$var])) {
            return null;
        }
        return $this->info[$var];
    }

    function getInfo()
    {
        return $this->info;
    }

    function setInfo($info, $index)
    {
        foreach (array_keys($info) as $key) {
            if ($info[$key] === null) {
                unset($info[$key]);
            }
        }
        $this->info[$index] = $info;
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent->setInfo($this->info, $this->index);
            $this->parent->save();
            return;
        }
        $info = $this->info;
        if (count($info) == 1) {
            $info = $info[0];
        }
        $this->parent->{'rawuses' . $this->type} = $info;
    }
}
?>