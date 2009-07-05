<?php
namespace pear2\Pyrus\PackageFile\v2;
class Configureoption implements \ArrayAccess, \Iterator, \Countable
{
    protected $parent;
    protected $info;
    protected $index;
    function __construct($parent, array $info, $index = null)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->index = $index;
    }

    function current()
    {
        $info = current($this->info);
        foreach (array('name', 'default', 'prompt') as $key) {
            if (!array_key_exists($key, $info)) {
                $info[$key] = null;
            }
        }
        return new \pear2\Pyrus\PackageFile\v2\Configureoption($this, $info, key($this->info));
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
        return $info['name'];
    }

    function next()
    {
        return next($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function locateConfigureOption($name)
    {
        foreach ($this->info as $i => $dep)
        {
            if (isset($dep['name']) && $dep['name'] == $name) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use -> operator to access properties of a configureoption');
        }
        $i = $this->locateConfigureOption($var);
        if (false === $i) {
            $i = count($this->info);
            $info = array('name' => $var, 'default' => null, 'prompt' => null);
        } else {
            $info = $this->info[$i];
            foreach (array('name', 'default', 'prompt') as $key) {
                if (!array_key_exists($key, $this->info[$i])) {
                    $info[$key] = null;
                }
            }
        }
        return new \pear2\Pyrus\PackageFile\v2\Configureoption($this, $info, $i);
    }

    function offsetSet($var, $value)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use -> operator to access properties of a configureoption');
        }
        if (!($value instanceof \pear2\Pyrus\PackageFile\v2\Configureoption)) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'Can only set configureoption to a \pear2\Pyrus\PackageFile\v2\Configureoption object');
        }
        $i = $this->locateConfigureOption($var);
        if (false === $i) {
            $i = count($this->info);
        }
        $this->info[$i] = array('name' => $var,
                                'default' => $value->default,
                                'prompt' => $value->prompt);
        $this->save();
    }

    function offsetExists($var)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use -> operator to access properties of a configureoption');
        }
        $i = $this->locateConfigureOption($var);
        return $i !== false;
    }

    function offsetUnset($var)
    {
        if ($this->index !== null) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use -> operator to access properties of a configureoption');
        }
        $i = $this->locateConfigureOption($var);
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
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use [] operator to access configureoption' . 's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
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
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use [] operator to access configureoption' . 's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
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
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use [] operator to access configureoptions');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception('Unknown variable ' . $var .
                                                                           ', must be one of ' .
                            implode(', ', array_keys($this->info)));
        }
        if ($args[0] === null) {
            $this->info[$var] = null;
            $this->save();
            return $this;
        }
        $this->info[$var] = $args[0];
        $this->save();
        return $this;
    }

    function __get($var)
    {
        if ($this->index === null) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception(
                'use [] operator to access configureoptions');
        }
        if ($var === 'type') {
            return 'configureoption';
        }
        if (!array_key_exists($var, $this->info)) {
            throw new \pear2\Pyrus\PackageFile\v2\Configureoption\Exception('Unknown variable ' . $var .
                                                                           ', must be one of ' .
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
        $this->parent->rawconfigureoption = $info;
    }
}
?>