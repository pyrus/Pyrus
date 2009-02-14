<?php
/**
 * PEAR2_Pyrus_PackageFile_v2_Releaseinstallcondition
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Manage a release's installation conditions in package.xml
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition implements ArrayAccess
{
    protected $parent;
    protected $index = null;
    protected $installcondition;
    protected $info = array();
    function __construct($parent, array $info, $condition = null, $index = null)
    {
        $this->parent = $parent;
        $this->info = $info;
        if ($condition !== null) {
            $this->installcondition = $condition;
        }
        if ($index !== null) {
            $this->index = $index;
        } elseif ($condition !== null) {
            if ($condition === 'extension') {
                if (!isset($info[0])) {
                    $this->info = array($info);
                }
            }
        }
    }

    function getInfo()
    {
        return $this->info;
    }

    function __get($var)
    {
        if ($var === 'installcondition') {
            return isset($this->installcondition) ? $this->installcondition : false;
        }
        if (isset($this->info[$var])) {
            if ($var === 'conflicts') {
                return $this->info[$var] !== null;
            }
            return $this->info[$var];
        }
        if ($var === 'conflicts') {
            return false;
        }
        return null;
    }

    function __call($var, $args)
    {
        if (!isset($this->installcondition)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot set variables for unknown install condition');
        }
        if (array_key_exists($var, $this->info)) {
            if (!count($args) && $var == 'conflicts') {
                $args = array('');
            }
            $this->info[$var] = $args[0];
            $this->save();
        }
        return $this;
    }

    function offsetGet($var)
    {
        if (isset($this->installcondition) && !isset($this->index) && $this->installcondition == 'extension') {
            if (!is_int($var)) {
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Choose which extension condition to access');
            }
            if (!isset($this->info[$var])) {
                if ($var != count($this)) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception(
                        'Can only set the ' .
                        'next highest release index ' . count($this) . ', not ' . $var);
                }
                $this->info[$var] = array();
            } else {
                foreach (array('name' => null, 'min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null) as $key => $val) {
                    if (!array_key_exists($key, $this->info[$var])) {
                        $this->info[$var][$key] = null;
                    }
                }
            }
            return new PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition($this,
                       $this->info[$var], 'extension', $var);
        }
        if (!is_string($var)) {
            throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot access numeric index');
        }
        $var = strtolower($var);
        switch ($var) {
            case 'php' :
                if (!isset($this->info[$var])) {
                    $this->info[$var] = array('min' => null, 'max' => null, 'exclude' => null);
                } else {
                    foreach (array('min' => null, 'max' => null, 'exclude' => null) as $key => $val) {
                        if (!array_key_exists($key, $this->info[$var])) {
                            $this->info[$var][$key] = null;
                        }
                    }
                }
                break;
            case 'os' :
                if (!isset($this->info[$var])) {
                    $this->info[$var] = array('name' => null, 'conflicts' => null);
                } else {
                    settype($this->info[$var], 'array');
                    foreach (array('name' => null, 'conflicts' => null) as $key => $val) {
                        if (!array_key_exists($key, $this->info[$var])) {
                            $this->info[$var][$key] = null;
                        }
                    }
                }
                break;
            case 'arch' :
                if (!isset($this->info[$var])) {
                    $this->info[$var] = array('pattern' => null, 'conflicts' => null);
                } else {
                    foreach (array('pattern' => null, 'conflicts' => null) as $key => $val) {
                        if (!array_key_exists($key, $this->info[$var])) {
                            $this->info[$var][$key] = null;
                        }
                    }
                }
                break;
            case 'extension' :
                if (!isset($this->info[$var])) {
                    $this->info[$var] = array(array('name' => null, 'min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null));
                } else {
                    if (!isset($this->info['extension'][0])) {
                        $this->info['extension'] = array($this->info['extension']);
                    }
                    foreach (array('name' => null, 'min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null) as $key => $val) {
                        foreach ($this->info['extension'] as $index => $ext) {
                            if (!array_key_exists($key, $ext)) {
                                $this->info['extension'][$index][$key] = null;
                            }
                        }
                    }
                }
                break;
            default :
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot access unknown install condition ' . $var);
        }
        return new PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition($this, $this->info[$var],
                   $var);
    }

    protected function locateExtension($ext)
    {
        if (!isset($this->info['extension'])) {
            return false;
        }
        foreach ($this->info['extension'] as $i => $test) {
            if ($test['name'] === $ext) {
                return $i;
            }
        }
        return false;
    }

    function offsetSet($var, $value)
    {
        $info = array();
        switch ($var) {
            case 'php' :
                if (is_string($value)) {
                    $info['min'] = $value;
                }
                break;
            case 'arch' :
                if (is_string($value)) {
                    $info['pattern'] = $value;
                }
                break;
            case 'os' :
                if (is_string($value)) {
                    $info['name'] = $value;
                }
                break;
            case 'extension' :
                if (!($value instanceof PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition)) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot set extension to anything but a' .
                                ' PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition object');
                }
                break;
            default :
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Unknown installcondition ' .
                    $var);
        }
        if ($value instanceof PEAR2_Pyrus_PackageFile_v2_Release_InstallCondition && $value->installcondition) {
            if ($value->installcondition != $var) {
                throw new PEAR2_Pyrus_PackageFile_v2_Release_Exception('Cannot set ' . $var .
                            ' to another install condition (' . $value->installcondition . ')');
            }
            if ($var != 'extension') {
                foreach ($this->info[$var] as $n => $unused) {
                    $this->info[$var][$n] = $value->$n;
                }
            } else {
                $ext = $this->locateExtension($value->name);
                if (!$ext) {
                    $ext = count($this->info['extension']);
                    $this->info['extension'][$ext] = array('name' => null, 'min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null);
                }
                foreach ($this->info['extension'][$ext] as $n => $unused) {
                    $this->info[$var][$n] = $value->$n;
                }
            }
        } else {
            foreach ($info as $key => $val) {
                $this->info[$var][$key] = $val;
            }
        }
        $this->save();
    }

    function offsetExists($var)
    {
        return isset($this->info[$var]);
    }

    function offsetUnset($var)
    {
        unset($this->info[$var]);
    }

    function setInstallCondition($obj, $type, $index = null)
    {
        if ($index !== null) {
            $this->info[$index] = $obj->getInfo();
            return $this->parent->setInstallCondition($this, $type);
        }
        if (isset($this->index)) {
            $this->parent->setInstallCondition($this, $type, $this->index);
        } else {
            if (!isset($this->info[$type])) {
                $this->info[$type] = array();
            }
            $this->info[$type] = $obj->getInfo();
            $this->parent->setInstallCondition($this);
        }
    }

    function save()
    {
        if (isset($this->installcondition)) {
            $this->parent->setInstallCondition($this, $this->installcondition, $this->index);
        } else {
            $this->parent->setInstallCondition($this);
        }
    }
}

?>