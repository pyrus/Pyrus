<?php
/**
 * This class is used for package, subpackage, and extension deps
 */
class PEAR2_Pyrus_PackageFile_v2_Dependencies_Package implements ArrayAccess, Iterator
{
    protected $info;
    protected $index = null;
    protected $parent;
    protected $type;
    protected $deptype;

    function __construct($deptype, $type, $parent, array $info, $index = null)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->index = $index;
        $this->type = $type;
        $this->deptype = $deptype;
    }

    function current()
    {
        $i = key($this->info);
        switch ($this->type) {
            case 'package' :
            case 'subpackage' :
                $keys = array('name' => null, 'channel' => null, 'uri' => null,
                                            'min' => null, 'max' => null,
                                            'recommended' => null, 'exclude' => null,
                                            'providesextension' => null, 'conflicts' => null);
                if ($this->deptype != 'required') {
                    unset($keys['conflicts']);
                }
                break;
            case 'extension' :
                $keys = array('name' => null, 'min' => null, 'max' => null,
                                            'recommended' => null, 'exclude' => null, 'conflicts' => null);
                if ($this->deptype != 'required') {
                    unset($keys['conflicts']);
                }
                break;
        }
        foreach ($keys as $key => $null) {
            if (!array_key_exists($key, $this->info[$i])) {
                $this->info[$i][$key] = null;
            }
        }
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package($this->deptype, $this->type, $this, $this->info[$i], $i);
    }

    function rewind()
    {
        reset($this->info);
    }

    function key()
    {
        $i = key($this->info);
        if ($this->type == 'extension') {
            return $this->info[$i]['name'];
        }
        $channel = isset($this->info[$i]['channel']) ? $this->info[$i]['channel'] : '__uri';
        return $channel . '/' . $this->info[$i]['name'];
    }

    function next()
    {
        return next($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function locateDep($name)
    {
        if ($this->type == 'extension') {
            foreach ($this->info as $i => $dep)
            {
                if (isset($dep['name']) && $dep['name'] == $name) {
                    return $i;
                }
            }
            return false;
        }
        $stuff = explode('/', $name);
        $name = array_pop($stuff);
        $channel = implode('/', $stuff);
        foreach ($this->info as $i => $dep)
        {
            if ($channel == '__uri') {
                if (isset($dep['name']) && $dep['name'] == $name && isset($dep['uri'])) {
                    return $i;
                }
                continue;
            }
            if (isset($dep['name']) && $dep['name'] == $name
                && isset($dep['channel']) && $dep['channel'] == $channel) {
                return $i;
            }
        }
        return false;
    }

    function offsetGet($var)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use -> operator to access dependency properties');
        }
        $i = $this->locateDep($var);
        if (false === $i) {
            $i = count($this->info);
            switch ($this->type) {
                case 'package' :
                case 'subpackage' :
                    if (!strpos($var, '/')) {
                        throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Cannot access "' . $var .
                            '", must use "channel/package" to specify a package dependency to access');
                    }
                    $stuff = explode('/', $var);
                    $name = array_pop($stuff);
                    $channel = implode('/', $stuff);
                    if ($channel === '__uri') {
                        // use fake uri, user must set it
                        $this->info[$i] = array('name' => $name, 'channel' => null, 'uri' => '__uri',
                                                    'min' => null, 'max' => null,
                                                    'recommended' => null, 'exclude' => null,
                                                    'providesextension' => null, 'conflicts' => null);
                    } else {
                        $this->info[$i] = array('name' => $name, 'channel' => $channel, 'uri' => null,
                                                    'min' => null, 'max' => null,
                                                    'recommended' => null, 'exclude' => null,
                                                    'providesextension' => null, 'conflicts' => null);
                    }
                    if ($this->deptype != 'required') {
                        unset($this->info[$i]['conflicts']);
                    }
                    break;
                case 'extension' :
                    $this->info[$i] = array('name' => $var, 'min' => null, 'max' => null,
                                                'recommended' => null, 'exclude' => null, 'conflicts' => null);
                    if ($this->deptype != 'required') {
                        unset($this->info[$i]['conflicts']);
                    }
                    break;
            }
        } else {
            switch ($this->type) {
                case 'package' :
                case 'subpackage' :
                    $keys = array('name' => $var, 'channel' => null, 'uri' => null,
                                                'min' => null, 'max' => null,
                                                'recommended' => null, 'exclude' => null,
                                                'providesextension' => null, 'conflicts' => null);
                    if ($this->deptype != 'required') {
                        unset($keys['conflicts']);
                    }
                    break;
                case 'extension' :
                    $keys = array('name' => $var, 'min' => null, 'max' => null,
                                                'recommended' => null, 'exclude' => null, 'conflicts' => null);
                    if ($this->deptype != 'required') {
                        unset($keys['conflicts']);
                    }
                    break;
            }
            foreach ($keys as $key => $null) {
                if (!array_key_exists($key, $this->info[$i])) {
                    $this->info[$i][$key] = null;
                }
            }
        }
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package($this->deptype, $this->type, $this, $this->info[$i], $i);
    }

    function offsetSet($var, $value)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use -> operator to access dependency properties');
        }
        if (!($value instanceof self)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Can only set $pf->dependencies[\'' .
                $this->deptype . '\']->' . $this->type . '[\'' . $var .
                '\'] to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object');
        }
        if ($this->type !== $value->type) {
            if (!(($this->type === 'package' && $value->type === 'subpackage') ||
                ($this->type === 'subpackage' && $value->type === 'package'))) {
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Cannot set ' . $this->type .
                    ' dependency to ' . $value->type . ' dependency');
            }
        }
        if ($var === null) {
            if ($this->type === 'extension') {
                $var = $value->name;
            } else {
                $var = $value->channel . '/' . $value->name;
            }
        }
        if ($this->type !== 'extension' && !strpos($var, '/')) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Cannot set "' . $var .
                '", must use "channel/package" to specify a package dependency to set');
        }
        if ($this->type === 'extension') {
            if ($value->name != $var) {
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Cannot set ' .
                    $var . ' to ' .
                    $value->name .
                    ', use $pf->dependencies[\'' .
                    $this->deptype . '\']->extension[] to set a new value');
            }
        } else {
            $stuff = explode('/', $var);
            $name = array_pop($stuff);
            $channel = implode('/', $stuff);
            if ($value->name != $name || $value->channel != $channel) {
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Cannot set ' .
                    $channel . '/' . $name . ' to ' .
                    $value->channel . '/' . $value->name .
                    ', use $pf->dependencies[\'' .
                    $this->deptype . '\']->' . $this->type . '[] to set a new value');
            }
        }
        if (false === ($i = $this->locateDep($var))) {
            $i = count($this->info);
        }
        $this->info[$i] = $value->getInfo();
        $this->save();
    }

    function offsetExists($var)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use -> operator to access dependency properties');
        }
        $i = $this->locateDep($var);
        return $i !== false;
    }

    function offsetUnset($var)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use -> operator to access dependency properties');
        }
        $i = $this->locateDep($var);
        if ($i === false) {
            return;
        }
        unset($this->info[$i]);
        $this->info = array_values($this->info);
        $this->save();
    }

    function __get($var)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use [] operator to access ' . $this->type .
                                                                        's');
        }
        if ($var == 'conflicts') {
            return isset($this->info[$var]);
        }
        if ($var === 'deptype') {
            return $this->deptype;
        }
        if ($var === 'type') {
            return $this->type;
        }
        if ($var === 'channel' && isset($this->info['uri'])) {
            return '__uri';
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

    function __isset($var)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use [] operator to access ' . $this->type .
                                                                        's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', array_keys($this->info))
            );
        }
        return isset($this->info[$var]);
    }

    function __unset($var)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use [] operator to access ' . $this->type .
                                                                        's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', array_keys($this->info))
            );
        }
        $this->info[$var] = null;
    }

    function __set($var, $value)
    {
        return $this->__call($var, array($value));
    }

    function __call($var, $args)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('Use [] operator to access ' . $this->type .
                                                                        's');
        }
        if (!array_key_exists($var, $this->info)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Unknown variable ' . $var . ', should be one of ' . implode(', ', array_keys($this->info))
            );
        }
        if (count($args) && $args[0] !== null && $var === 'channel' && isset($this->info['uri'])) {
            $this->info['uri'] = null;
            $this->info['channel'] = $args[0];
            $this->save();
            return;
        }
        if (count($args) && $args[0] !== null && $var === 'uri' && isset($this->info['channel'])) {
            $this->info['channel'] = null;
            $this->info['uri'] = $args[0];
            $this->save();
            return;
        }
        if ($var === 'name' || $var === 'channel') {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Cannot change dependency name, use unset() to remove the old dependency'
            );
        }
        if ($var == 'conflicts') {
            if (count($args)) {
                if ($args[0]) {
                    $this->info['conflicts'] = '';
                } else {
                    $this->info['conflicts'] = null;
                }
            } else {
                $this->info['conflicts'] = '';
            }
            $this->save();
            return $this;
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
                if (!is_array($this->info[$var])) {
                    $this->info[$var] = array($this->info[$var]);
                }
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
                continue;
            }
            if (is_array($null) && count($null) == 1) {
                $info[$key] = $null[0];
            }
        }
        $this->info[$index] = $info;
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent->setInfo($this->index, $this->info);
        } else {
            $info = $this->info;
            if (count($info) == 1) {
                $info = $info[0];
            }
            $this->parent->setInfo($this->type, $info);
        }
        $this->parent->save();
    }
}
?>