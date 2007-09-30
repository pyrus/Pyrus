<?php
/**
 * Manage dependencies
 * 
 * To be used like:
 * <code>
 * // reset deps
 * $pf->dependencies = null;
 * // for PHP dep
 * // defaults to min
 * $pf->dependencies->required->php = '5.3.0';
 * $pf->dependencies->required->php = array('min' => '5.3.0', 'max' => '7.0.0',
 *      'exclude' => array('6.1.2'));
 * // for PEAR Installer dep
 * // defaults to min
 * $pf->dependencies->required->pearinstaller = '2.0.0';
 * $pf->dependencies->required->pearinstaller = array('min' => '2.0.0');
 * // for required/optional package deps or subpackage deps
 * $pf->dependencies->required->package['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'));
 * $pf->dependencies->optional->package['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'));
 * $pf->dependencies->required->subpackage['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'));
 * // for conflicting package dep
 * $pf->dependencies->required->subpackage['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'), 'conflicts' => '');
 * // for PECL extension deps (optional or required same as packages)
 * $pf->dependencies->required->package['channel/PackageName'] =
 *      array('min' => '1.1.0', 'max' => '1.2.0', 'recommended' => '1.1.1',
 *            'exclude' => array('1.1.0a1', '1.1.0a2'), 'providesextension' => 'packagename');
 * // for extension deps (required or optional same as packages)
 * $pf->dependencies->required->extension['extension'] =
 *      array('min' => '1.0.0', 'max' => '1.2.0', 'recommended' => '1.1.1');
 * // for regular arch deps
 * $pf->dependencies->required->arch['i386'] = true // only works on i386
 * // for conflicting arch deps
 * $pf->dependencies->required->arch['*(ix|ux)'] = false // doesn't work on unix/linux
 * // for regular OS deps
 * $pf->dependencies->required->os['windows'] = true; // only works on windows
 * // for conflicting OS deps
 * $pf->dependencies->required->os['freebsd'] = false; // doesn't work on FreeBSD
 * 
 * // dependency group setup
 * $group = $pf->dependencies->group['name']->hint('Install optional stuff as a group');
 * $group->package['channel/PackageName1'] = array();
 * $group->package['channel/PackageName2'] = array('min' => '1.2.0');
 * $group->subpackage['channel/PackageName3'] = array('recommended' => '1.2.1');
 * $group->extension['extension'] = array();
 * </code>
 */
class PEAR2_Pyrus_PackageFile_v2_Dependencies implements ArrayAccess, Iterator, Countable
{
    private $_parent;
    private $_packageInfo;
    private $_required;
    private $_group = null;
    private $_package;
    private $_type;
    private $_pos = 0;
    private $_count = 0;
    private $_info = array();
    function __construct(array &$parent, array &$packageInfo, $required = null, $type = null,
                         $package = null, $group = null)
    {
        $this->_parent = &$parent;
        $this->_packageInfo = &$packageInfo;
        if (!$required) return;
        if (!in_array($required, array('required', 'optional', 'group'), true)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Internal error: $required is not required/optional/group');
        }
        $this->_required = $required;
        if (!isset($parent[$required])) {
            $parent[$required] = array();
            $this->_packageInfo = &$parent;
        }
        if ($this->_required != 'group' && $group) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Internal error: $group passed into required dependency');
        } elseif ($group) {
            if (!is_string($group)) {
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                    'Internal error: $group must be a string');
            }
            $this->_packageInfo = &$parent['group'];
            $this->_group = $group;
            // locate group in the xml and initialize if not present
            if (!count($this->_packageInfo)) {
                $this->_packageInfo =
                    array('attribs' => array('name' => $group, 'hint' => ''));
            } elseif (!isset($this->_packageInfo[0])) {
                if ($this->_packageInfo['attribs']['name'] != $group) {
                    $this->_packageInfo = array($this->_packageInfo,
                        array('attribs' => array('name' => $group, 'hint' => '')));
                    $this->_packageInfo = &$this->_packageInfo[1];
                }
            } else {
                $found = false;
                foreach ($this->_packageInfo as $i => $g) {
                    if ($g['attribs']['name'] == $group) {
                        $this->_packageInfo = &$this->_packageInfo[$i];
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $this->_packageInfo[$i = count($this->_packageInfo)] =
                        array('attribs' => array('name' => $group, 'hint' => ''));
                }
            }
        } elseif (!$type) {
            $this->_packageInfo = &$this->_packageInfo[$this->_required];
        }
        if (!$type) {
            if (count($this->_packageInfo)) {
                if (isset($this->_packageInfo[0])) {
                    $this->_count = count($this->_packageInfo);
                } else {
                    $this->_count = 1;
                }
            }
            return;
        }
        if (!is_string($type)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                        'Internal error: $type is not a string, but is a ' . gettype($type));
        }
        if ($required == 'required') {
            switch ($type) {
                case 'php' :
                case 'pearinstaller' :
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                case 'arch' :
                case 'os' :
                    $this->_type = $type;
                    if (!isset($this->_packageInfo[$type])) {
                        $this->_packageInfo[$type] = array();
                    }
                    $this->_packageInfo = &$this->_packageInfo[$type];
                    break;
                default :
                    throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                        'Unknown dependency type ' . $type);
            }
        } else {
            switch ($type) {
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                    $this->_type = $type;
                    if (!isset($this->_packageInfo[$type])) {
                        $this->_packageInfo[$type] = array();
                    }
                    $this->_packageInfo = &$this->_packageInfo[$type];
                    break;
                case 'php' :
                case 'pearinstaller' :
                case 'arch' :
                case 'os' :
                default :
                    throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                        $type . ' dependency cannot be optional');
            }
        }
        if (count($this->_packageInfo)) {
            if (isset($this->_packageInfo[0])) {
                $this->_count = count($this->_packageInfo);
            } else {
                $this->_count = 1;
            }
        } else {
            $this->_count = 0;
        }
        if (!$package) return;
        switch ($this->_type) {
            case 'php' :
            case 'pearinstaller' :
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                    'Internal error: $package passed into ' . $type . ' dependency');
            case 'package' :
            case 'subpackage' :
                $channel = explode('/', $package);
                $package = array_pop($channel);
                $channel = implode('/', $channel);
                $this->_info['name'] = $package;
                $this->_info['channel'] = $channel;
                $this->_package = true;
                break;
            case 'os' :
            case 'extension' :
                $this->_info['name'] = $package;
                $this->_package = true;
                break;
            case 'arch' :
                $this->_info['pattern'] = $package;
                $this->_package = true;
                break;
        }
        if (!isset($this->_packageInfo[0])) {
            $name = ($this->_type == 'arch') ? 'pattern' : 'name';
            if ($this->_packageInfo[$name] != $package) {
                $this->_packageInfo = array($this->_packageInfo, array($name => $package));
                $this->_packageInfo = &$this->_packageInfo[1];
            }
        } else {
            foreach ($this->_packageInfo as $i => $dep) {
                if ($dep[$name] == $package) {
                    $this->_packageInfo = &$this->_packageInfo[$i];
                    return;
                }
            }
            $this->_packageInfo[$i = count($this->_packageInfo)] = array($name => $package);
            $this->_packageInfo = &$this->_packageInfo[$i];
        }
    }

    function __get($var)
    {
        if (!isset($this->_required)) {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies($this->_parent,
                $this->_packageInfo, $var);
        }
        if (!isset($this->_type)) {
            if (isset($this->_group)) {
                return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                    $this->_parent, $this->_packageInfo, $this->_required,
                    $var, null, $this->_group);
            } else {
                return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                    $this->_parent, $this->_packageInfo, $this->_required,
                    $var);
            }
        }
        if ($this->_type == 'group' && !isset($this->_group)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Dependency group must be accessed like $pf->group[\'groupname\']');
        }
        if (!isset($this->_package) && $this->_type != 'php' && $this->_type != 'pearinstaller') {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                $this->_parent, $this->_packageInfo[$var], $this->_required,
                $this->_type, $var);
        }
        if (!isset($this->_packageInfo[$var])) {
            return null;
        }
        return $this->_packageInfo[$var];
    }

    function __set($var, $value)
    {
        if (isset($this->_required) && $this->_required == 'required'
              && in_array($var, array('php', 'pearinstaller'), true)) {
            if (is_string($value)) {
                $value = array('min' => $value);
            }
            if (!is_array($value)) {
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                    $var . ' dependency must be an array, was a ' . gettype($value));
            }
            $info = array();
            foreach (array('min', 'max', 'exclude') as $index) {
                if (isset($value[$index])) {
                    $info[$index] = $value[$index];
                }
            }
            $this->_packageInfo[$var] = $info;
            return;
        }
        throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
            'Cannot set ' . $var . ' directly');
    }

    function current()
    {
        if (!$this->valid()) return null;
        if ($this->_required === 'group' && !isset($this->_group)) {
            if (!isset($this->_type)) {
                if (!isset($this->_packageInfo[0])) {
                    return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                        $this->_parent, $this->_packageInfo, $this->_required,
                        $this->_type, null, $this->_packageInfo['attribs']['name']);
                } else {
                    return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                        $this->_parent, $this->_packageInfo, $this->_required,
                        $this->_type, null, $this->_packageInfo[$this->_pos]['attribs']['name']);
                }
            }
        }
        if (!isset($this->_packageInfo[0])) {
            return $this->_packageInfo;
        }
        return $this->_packageInfo[$this->_pos];
    }

    function next()
    {
        $this->_pos++;
    }

    function key()
    {
        if (!$this->valid()) return null;
        return $this->_pos;
    }

    function valid()
    {
        if (!$this->_count) {
            return false;
        }
        return $this->_pos < $this->_count;
    }

    function rewind()
    {
        $this->_pos = 0;
    }

    function count()
    {
        return $this->_count;
    }

    function hint($hint)
    {
        if ($this->_required !== 'group' || !$this->_group) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'hint can only be set for dependency groups');
        }
        $this->_packageInfo['attribs']['hint'] = $hint;
        return $this;
    }

    function offsetGet($var)
    {
        if ($this->_required == 'group' && !isset($this->_group)) {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
                $this->_parent, $this->_packageInfo, $this->_required,
                $this->_type, null, $var);
        }
        if (isset($this->_type) && in_array($this->_type, array('php', 'pearinstaller', 'arch', 'os'))) {
            return $this->_packageInfo[$var];
        }
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies(
            $this->_parent, $this->_packageInfo, $this->_required,
            $this->_type, $var, $this->_group);
    }

    function offsetSet($var, $value)
    {
        if (!isset($this->_required)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Compatible_Exception(
                        'Cannot set ' . $var . ' directly, must specify required, optional, or group dependency first');
        }
        if (isset($this->_type) && in_array($this->_type, array('package', 'subpackage'))) {
            if (!is_array($value)) {
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                    $this->_type . ' dependency must be an array, was a ' . gettype($value));
            }
            $channel = explode('/', $var);
            $package = array_pop($channel);
            $channel = implode('/', $channel);
            $info = array('name' => $package, 'channel' => $channel);
            foreach (array('min', 'max', 'recommended', 'exclude', 'nodefault',
                           'conflicts', 'providesextension') as $index) {
                if (isset($value[$index])) {
                    $info[$index] = $value[$index];
                }
            }
            if (isset($this->_packageInfo[0])) {
                foreach ($this->_packageInfo as $i => $dep) {
                    if ($dep['package'] === $package && $dep['channel'] === $channel) {
                        $this->_packageInfo[$i] = $info;
                        return;
                    }
                }
                $this->_packageInfo[] = $info;
            } else {
                if (!count($this->_packageInfo)) {
                    $this->_packageInfo = $info;
                    return;
                }
                if ($this->_packageInfo['name'] === $package
                      && $this->_packageInfo['channel'] === $channel) {
                    $this->_packageInfo = $info;
                } else {
                    $this->_packageInfo = array($this->_packageInfo, $info);
                }
            }
            return;
        }
        if (isset($this->_type) && $this->_type === 'extension') {
            if (!is_array($value)) {
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                    $this->_type . ' dependency must be an array, was a ' . gettype($value));
            }
            $info = array('name' => $var);
            foreach (array('min', 'max', 'recommended', 'exclude', 'conflicts') as $index) {
                if (isset($value[$index])) {
                    $info[$index] = $value[$index];
                }
            }
            if (isset($this->_packageInfo[0])) {
                foreach ($this->_packageInfo as $i => $dep) {
                    if ($dep['name'] === $var) {
                        $this->_packageInfo[$i] = $info;
                        return;
                    }
                }
                $this->_packageInfo[] = $info;
            } else {
                if (!count($this->_packageInfo)) {
                    $this->_packageInfo = $info;
                } elseif ($this->_packageInfo['name'] === $var) {
                    $this->_packageInfo = $info;
                } else {
                    $this->_packageInfo = array($this->_packageInfo, $info);
                }
            }
            return;
        }
        if (isset($this->_type) && in_array($this->_type, array('arch', 'os'))) {
            $val = (bool) $value;
            $info = array(($this->_type === 'arch' ? 'pattern' : 'name') => $var);
            if (!$val) {
                $info['conflicts'] = '';
            }
            if (isset($this->_packageInfo[0])) {
                foreach ($this->_packageInfo as $index => $dep) {
                    if ($dep[$name] == $var) {
                        $this->_packageInfo[$index] = $info;
                        return;
                    }
                }
                $this->_packageInfo[] = $info;
            } else {
                if (!count($this->_packageInfo) || $this->_packageInfo[$name] === $var) {
                    $this->_packageInfo = $info;
                } else {
                    $this->_packageInfo = array($this->_packageInfo, $info);
                }
            }
        }
    }

    /**
     * unimplemented
     * @param string $var
     */
    function offsetUnset($var)
    {
    }

    /**
     * unimplemented
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
        return isset($this->_packageInfo[$var]);
    }
}