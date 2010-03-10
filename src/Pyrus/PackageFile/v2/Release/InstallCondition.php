<?php
/**
 * \pear2\Pyrus\PackageFile\v2\Releaseinstallcondition
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Manage a release's installation conditions in package.xml
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\v2\Release;
class InstallCondition implements \ArrayAccess
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
            throw new Exception('Cannot set variables for unknown install condition');
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
            if (!is_string($var)) {
                throw new Exception('Access extension installconditions by name, not number');
            }

            if (false === ($i = $this->locateExtension($var))) {
                $i = count($this->info);
                $this->info[$i] =
                    array('name' => $var, 'min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null);
            } else {
                foreach (array('name' => null, 'min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null) as $key => $val) {
                    if (!array_key_exists($key, $this->info[$i])) {
                        $this->info[$i][$key] = null;
                    }
                }
            }

            return new InstallCondition($this, $this->info[$i], 'extension', $i);
        }

        if (!is_string($var)) {
            throw new Exception('Cannot access numeric index');
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
                    $this->info[$var] = array();
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
                throw new Exception('Cannot access unknown install condition ' . $var);
        }

        return new InstallCondition($this, $this->info[$var], $var);
    }

    protected function locateExtension($ext)
    {
        foreach ($this->info as $i => $test) {
            if (!isset($test['name'])) {
                continue;
            }

            if ($test['name'] === $ext) {
                return $i;
            }
        }

        return false;
    }

    function offsetSet($var, $value)
    {
        $info = array();
        if (isset($this->installcondition) && $this->installcondition == 'extension') {
            if (!($value instanceof InstallCondition)) {
                throw new Exception('Cannot set extension to anything but a' .
                            ' \pear2\Pyrus\PackageFile\v2\Release\InstallCondition object');
            }
        } else {
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
                    throw new Exception('Set extension install condition by name');
                default :
                    throw new Exception('Unknown install condition ' . $var);
            }
        }

        if ($value instanceof InstallCondition && $value->installcondition) {
            if (!isset($this->installcondition) || $this->installcondition != 'extension') {
                if ($value->installcondition != $var) {
                    throw new Exception('Cannot set ' . $var .
                                ' to another install condition (' . $value->installcondition . ')');
                }

                if (!isset($this->info[$var])) {
                    switch ($var) {
                        case 'php' :
                            $this->info[$var] = array('min' => null, 'max' => null, 'exclude' => null);
                            break;
                        case 'os' :
                            $this->info[$var] = array('name' => null, 'conflicts' => null);
                            break;
                        case 'arch' :
                            $this->info[$var] = array('pattern' => null, 'conflicts' => null);
                            break;
                    }
                }

                foreach ($this->info[$var] as $n => $unused) {
                    $this->info[$var][$n] = $value->$n;
                }
            } else {
                if ($value->installcondition != 'extension') {
                    throw new Exception('Cannot set extension ' . $var .
                                ' to another install condition (' . $value->installcondition . ')');
                }

                $ext = $this->locateExtension($var);
                if (!$ext) {
                    $ext = count($this->info);
                    $this->info[$ext] = array('name' => $var, 'min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null);
                }

                foreach (array('min' => null, 'max' => null, 'exclude' => null, 'conflicts' => null) as $n => $unused) {
                    if ($n == 'conflicts') {
                        if ($value->conflicts) {
                            $this->info[$ext]['conflicts'] = '';
                        } else {
                            $this->info[$ext]['conflicts'] = null;
                        }
                        continue;
                    }

                    $this->info[$ext][$n] = $value->$n;
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
        if ($this->installcondition == 'extension' && !isset($this->index)) {
            return ($this->locateExtension($var) !== false);
        }

        return isset($this->info[$var]);
    }

    function offsetUnset($var)
    {
        if ($this->installcondition == 'extension' && !isset($this->index)) {
            if (($i = $this->locateExtension($var)) !== false) {
                unset($this->info[$i]);
            }

            return;
        }

        unset($this->info[$var]);
        $this->save();
    }

    function setInstallCondition($obj, $type, $index = null)
    {
        if (!$this->parent) {
            return;
        }

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
        if (!$this->parent) {
            return;
        }

        if (isset($this->installcondition)) {
            $this->parent->setInstallCondition($this, $this->installcondition, $this->index);
        } else {
            $this->parent->setInstallCondition($this);
        }
    }
}