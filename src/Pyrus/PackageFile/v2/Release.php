<?php
/**
 * Manage a release in package.xml
 * 
 * To be used like:
 *
 * <code>
 * // release is a PHP package
 * $pf->type = 'php';
 * // release is an extension source package
 * $pf->type = 'extsrc';
 * $pf->release[0]->installconditions['php'] = array('min' => '5.2.0');
 * // defaults to "min"
 * $pf->release[0]->installconditions['php'] = '5.2.0';
 * // defaults to "pattern"
 * $pf->release[0]->installconditions['arch'] = 'i386';
 * $pf->release[0]->installconditions['arch'] =
 *      array('pattern' => 'i386', 'conflicts' => 'yes');
 * // defaults to "name"
 * $pf->release[0]->installconditions['os'] = 'windows';
 * // defaults to existing
 * $pf->release[0]->installconditions['extension']['PDO'] = array();
 * $pf->release[0]->installconditions['extension']['PDO'] =
 *      array('min' => '1.0');
 * $pf->release[0]->ignore('path/to/file.ext');
 * $pf->release[0]->installAs('path/to/anotherfile.ext', 'new/name.php');
 * // add another release
 * $i = count($pf->release);
 * $pf->release[$i]->ignore('path/to/anotherfile.ext');
 * $pf->release[$i]->installAs('path/to/file.ext', 'new/name.php');
 * // remove release
 * unset($pf->release[1]);
 * // remove all releases
 * $pf->release = null;
 * </code>
 */
class PEAR2_Pyrus_PackageFile_v2_Release implements ArrayAccess, Countable
{
    private $_parent;
    private $_packageInfo;
    private $_filelist;
    private $_installcondition;
    function __construct(array &$parent, array &$packageInfo, array &$filelist,
                         $installcondition = null)
    {
        $this->_parent = &$parent;
        $this->_packageInfo = &$packageInfo;
        $this->_filelist = &$filelist;
        $this->_installcondition = $installcondition;
    }

    function __get($var)
    {
        if ($var === 'installconditions') {
            return new PEAR2_Pyrus_PackageFile_v2_Release($this->_parent,
                       $this->_packageInfo[$var], $this->_filelist, true);
        }
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown variable ' . $var .
            ', installconditions is the only supported variable');
    }

    function count()
    {
        if (isset($this->_packageInfo[0])) {
            return count($this->_packageInfo);
        }
        return count($this->_packageInfo) ? 1 : 0;
    }

    function offsetGet($var)
    {
        if (is_int($var) && !$this->_installcondition) {
            if (isset($this->_packageInfo[0])) {
                if (!isset($this->_packageInfo[$var])) {
                    if ($var != count($this)) {
                        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception(
                            'Can only set the ' .
                            'next highest release index ' . count($this) . ', not ' . $var);
                    }
                    $this->_packageInfo[$var] = array();
                }
                return new PEAR2_Pyrus_PackageFile_v2_Release($this->_parent,
                           $this->_packageInfo[$var], $this->_filelist);                
            } else {
                if (!$var) {
                    return $this;
                }
                if ($var != count($this)) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Can only set the ' .
                        'next highest release index ' . count($this) . ', not ' . $var);
                }
                $this->_packageInfo = array($this->_packageInfo, array());
                return new PEAR2_Pyrus_PackageFile_v2_Release($this->_parent,
                           $this->_packageInfo[$var], $this->_filelist);
            }
        }
        if (is_string($var) && $this->_installcondition == 'extension') {
            if (!isset($this->_packageInfo[$var])) {
                $this->_packageInfo[$var] = array();
            }
            return new PEAR2_Pyrus_PackageFile_v2_Release($this->_parent,
                       $this->_packageInfo[$var], $this->_filelist, 'extension');
        }
        if (!is_int($var) && !$this->_installcondition) {
            if (is_string($this->_installcondition)) {
                if (isset($this->_packageInfo[$var])) {
                    return $this->_packageInfo[$var];
                }
                return null;
            }
            if (in_array($var, array('php', 'os', 'arch', 'extension'), true)) {
                if (!isset($this->_packageInfo['installconditions'])) {
                    $this->_packageInfo['installconditions'] = array();
                }
                if (!isset($this->_packageInfo['installconditions'][$var])) {
                    $this->_packageInfo['installconditions'][$var] = array();
                }
                if ($var !== 'extension') {
                    return $this->_packageInfo['installconditions'][$var];
                }
                return new PEAR2_Pyrus_PackageFile_v2_Release($this->_parent,
                           $this->_packageInfo[$var], $this->_filelist, 'extension');
            }
        }
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot access numeric index of ' .
            $this->_installcondition . ' install condition');
    }

    private function _setExtension($info)
    {
        if (!isset($this->_packageInfo[0])) {
            if (!count($this->_packageInfo)) {
                $this->_packageInfo = $info;
                return;
            }
            if ($this->_packageInfo['name'] == $info['name']) {
                $this->_packageInfo = $info;
            } else {
                $this->_packageInfo = array($this->_packageInfo, $info);
            }
        } else {
            foreach ($this->_packageInfo as $i => $cond) {
                if ($dep['name'] === $info['name']) {
                    $this->_packageInfo[$i] = $info;
                    return;
                }
            }
            $this->_packageInfo[] = $info;
        }
    }

    function offsetSet($var, $value)
    {
        if (!isset($this->_installcondition)) {
            if ($var === null) {
                $var = 0;
            }
            if (is_int($var)) {
                if (!isset($this->_packageInfo[$var])) {
                    if (count($this->_packageInfo)) {
                        $this->_packageInfo = array($this->_packageInfo);
                    }
                    if (!isset($this->_packageInfo[$var])) {
                        $this->_packageInfo[$var] = array();
                    }
                }
                return new PEAR2_Pyrus_PackageFile_v2_Release($this->_parent,
                    $this->_packageInfo[$var], $this->_filelist);
            }
        }
        if ($this->_installcondition === 'extension') {
            if (!is_string($var)) {
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('extension names must be ' .
                    'strings for installconditions');
            }
            $info = array();
            if (is_array($value)) {
                foreach (array('name', 'min', 'max', 'exclude', 'conflicts') as $index) {
                    if (!isset($value[$index])) continue;
                    $info[$index] = $value[$index];
                }
            } elseif (is_string($value)) {
                $info = array('name' => $value);
            } else {
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('extension can only be set to' .
                    ' array or string');
            }
            $this->_setExtension($info);
            return;
        }
        switch ($var) {
            case 'php' :
                $info = array();
                if (is_array($value)) {
                    foreach (array('min', 'max', 'exclude') as $index) {
                        if (!isset($value[$index])) continue;
                        $info[$index] = $value[$index];
                    }
                } elseif (is_string($value)) {
                    $info = array('min' => $value);
                }
                $this->_packageInfo = $value;
                break;
            case 'arch' :
                $info = array();
                if (is_array($value)) {
                    foreach (array('pattern', 'conflicts') as $index) {
                        if (!isset($value[$index])) continue;
                        $info[$index] = $value[$index];
                    }
                } elseif (is_string($value)) {
                    $info = array('pattern' => $value);
                }
                $this->_packageInfo = $value;
                break;
            case 'os' :
                $info = array();
                if (is_array($value)) {
                    foreach (array('name', 'conflicts') as $index) {
                        if (!isset($value[$index])) continue;
                        $info[$index] = $value[$index];
                    }
                } elseif (is_string($value)) {
                    $info = array('name' => $value);
                }
                $this->_packageInfo = $value;
                break;
            case 'extension' :
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Use [\'extensionname\'] to set' .
                ' an extension\'s installcondition');
            default :
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown installcondition ' .
                    $var);
        }
    }

    /**
     * @param string $var
     */
    function offsetUnset($var)
    {
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('unset not supported');
    }

    /**
     * @param string $var
     * @return bool
     */
    function offsetExists($var)
    {
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('isset not supported');
    }

    function ignore($file)
    {
        if ($this->_installcondition) {
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('file ignore is not supported' .
                ' within installconditions');
        }
        if (isset($this->_filelist[$file])) {
            if (!isset($this->_packageInfo['ignore'])) {
                $this->_packageInfo['ignore'] = array('attribs' => array('name' => $file));
                return;
            }
            if (!isset($this->_packageInfo['ignore'][0])) {
                $this->_packageInfo['ignore'] = array($this->_packageInfo['ignore'],
                    array('attribs' => array('name' => $file)));
            }
            $this->_packageInfo['ignore'][] = array('attribs' => array('name' => $file));
        }
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown file ' . $file .
            ' - add to filelist before ignoring');
    }

    function installAs($file, $newname)
    {
        if (!is_string($file) || !is_string($newname)) {
            throw BadMethodCallException('$file and $newname must be strings');
        }
        if ($this->_installcondition) {
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('file ignore is not supported' .
                ' within installconditions');
        }
        if (isset($this->_filelist[$file])) {
            if (!isset($this->_packageInfo['install'])) {
                $this->_packageInfo['install'] = array('attribs' =>
                    array('name' => $file, 'as' => $newname));
                return;
            }
            if (!isset($this->_packageInfo['install'][0])) {
                $this->_packageInfo['install'] = array($this->_packageInfo['install'],
                    array('attribs' => array('name' => $file, 'as' => $newname)));
                return;
            }
            $this->_packageInfo['install'][] = array('attribs' =>
                array('name' => $file, 'as' => $newname));
            return;
        }
        throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown file ' . $file .
            ' - add to filelist before adding install as tag');
    }
}