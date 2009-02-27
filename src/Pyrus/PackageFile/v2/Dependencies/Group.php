<?php
class PEAR2_Pyrus_PackageFile_v2_Dependencies_Group implements Iterator
{
    protected $parent;
    protected $info = array();
    protected $index = null;

    function __construct($parent, $info, $index = null)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->index = $index;
    }

    function locateGroup($name)
    {
        if (count($this->info) && !isset($this->info[0])) {
            $this->info = array($this->info);
        }
        foreach ($this->info as $i => $group)
        {
            if (isset($group['attribs']) && isset($group['attribs']['name']) && $group['attribs']['name'] == $name) {
                return $i;
            }
        }
        return false;
    }

    function current()
    {
        $i = key($this->info);
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Group($this, $this->info[$i], $i);
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
        $i = key($this->info);
        return $this->info[$i]['attribs']['name'];
    }

    function next()
    {
        return next($this->info);
    }

    function valid()
    {
        return current($this->info);
    }

    function __get($group)
    {
        if (isset($this->index)) {
            if ($group == 'hint') {
                if (!isset($this->info['attribs']['hint'])) {
                    return null;
                }
                return $this->info['attribs']['hint'];
            }
            if ($group == 'name') {
                if (!isset($this->info['attribs']['name'])) {
                    return null;
                }
                return $this->info['attribs']['name'];
            }
            $var = $group;
            if (!isset($this->info[$var])) {
                switch ($var) {
                    case 'package' :
                    case 'subpackage' :
                    case 'extension' :
                        $this->info[$var] = array();
                        break;
                    default :
                        throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                                        'Only package, subpackage, and ' .
                                        'extension dependencies are supported in dependency groups, asked for ' .
                                        $var);
                }
            } else {
                switch ($var) {
                    case 'package' :
                    case 'subpackage' :
                    case 'extension' :
                        if (count($this->info[$var]) && !isset($this->info[$var][0])) {
                            $this->info[$var] = array($this->info[$var]);
                        }
                        break;
                }
            }
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package('group', $var, $this, $this->info[$var]);
        }
        $i = $this->locateGroup($group);
        if (false === $i) {
            $i = count($this->info);
            $this->info[] = array('attribs' => array('hint' => null, 'name' => $group));
        }
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Group($this, $this->info[$i], $i);
    }

    function __set($group, $value)
    {
        if (isset($this->index)) {
            if ($group == 'hint') {
                if ($value === null) {
                    if (!isset($this->info['attribs']) || !isset($this->info['attribs']['hint'])) {
                        return;
                    }
                    unset($this->info['attribs']['hint']);
                    $this->save();
                    return;
                }
                if (!is_string($value)) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('hint must be a string, was ' . gettype($value));
                }
                if (!isset($this->info['attribs'])) {
                    $this->info['attribs'] = array();
                }
                $this->info['attribs']['hint'] = $value;
                $this->save();
                return;
            }
            $info = array();
            switch ($group) {
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                    if ($value === null) {
                        unset($this->info[$group]);
                        $this->save();
                        return;
                    }
                    if (!($value instanceof PEAR2_Pyrus_PackageFile_v2_Dependencies_Package)) {
                        throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                            'Can only set ' . $group . ' to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object'
                        );
                    }
                    $this->info[$group] = $value->getInfo();
                    $this->save();
                    break;
                default :
                    throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                                    'Only package, subpackage, and ' .
                                    'extension dependencies are supported in dependency groups, asked for ' .
                                    $group);
            }
        }
    }

    function __isset($group)
    {
        if (isset($this->index)) {
            if ($group == 'hint') {
                return isset($this->info['attribs']['hint']);
            }
            return isset($this->info[$group]) && !empty($this->info[$group]);
        }
        return false !== $this->locateGroup($group);
    }

    function __unset($group)
    {
        if (isset($this->index)) {
            if ($group == 'hint') {
                $this->info['attribs']['hint'] = null;
                $this->save();
                return;
            }
            unset($this->info[$group]);
            $this->save();
            return;
        }
        $i = $this->locateGroup($group);
        if ($i !== false) {
            unset($this->info[$i]);
            $this->save();
        }
    }

    function getInfo()
    {
        return $this->info;
    }

    function setInfo($index, $info)
    {
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
            $this->parent->setInfo('group', $info);
        }
        $this->parent->save();
    }
}
?>