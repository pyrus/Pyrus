<?php
/**
 * \PEAR2\Pyrus\PackageFile\v2\Dependencies
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
 * Manage dependencies
 *
 * To be used like:
 * <code>
 * // reset deps
 * $reg->dependencies = null;
 * // for PHP dep
 * // defaults to min
 * $reg->dependencies['required']->php = '5.3.0';
 * $reg->dependencies['required']->php->min('5.3.1')->max('7.0.0')->exclude('6.1.2');
 *
 * $reg->dependencies['required']->php = null;
 *
 * $reg->dependencies['required']->php->exclude('6.1.2')->exclude('6.1.3');
 * $reg->dependencies['required']->php->exclude(null);
 *
 * // for PEAR Installer dep
 * // defaults to min
 * $reg->dependencies['required']->pearinstaller = '2.0.0';
 * $reg->dependencies['required']->pearinstaller->min('2.0.1')->max('3.0.0');
 *
 * // for required/optional package deps or subpackage deps
 * $reg->dependencies->required->package['channel/PackageName']->min('1.1.0')->max('1.2.0')->recommended('1.1.1')
 *     ->exclude('1.1.0a1')->exclude('1.1.0a2');
 * $reg->dependencies['optional']->package['channel/PackageName']->min('1.1.0')->max('1.2.0')->recommended('1.1.1')
 *     ->exclude('1.1.0a1')->exclude('1.1.0a2');
 *
 * $reg->dependencies->required->subpackage['channel/subpackageName']->min('1.1.0')->max('1.2.0')->recommended('1.1.1')
 *     ->exclude('1.1.0a1')->exclude('1.1.0a2');
 * $reg->dependencies->optional->subpackage['channel/subpackageName']->min('1.1.0')->max('1.2.0')->recommended('1.1.1')
 *     ->exclude('1.1.0a1')->exclude('1.1.0a2');
 *
 * // for conflicting package dep
 * $reg->dependencies['required']->subpackage['channel/PackageName']->conflicts();
 * $reg->dependencies['required']->subpackage['channel/PackageName']->conflicts(false);
 *
 * // for PECL extension deps (optional or required same as packages)
 * $reg->dependencies['required']->package['channel/PackageName']->providesextension('packagename');
 *
 * // for extension deps (required or optional same as packages)
 * $reg->dependencies['required']->extension['extname']->min('1.0.0')->max('1.2.0')->recommended('1.1.1');
 *
 * // for regular arch deps
 * $reg->dependencies['required']->arch['i386'] = true; // only works on i386
 *
 * // for conflicting arch deps
 * $reg->dependencies['required']->arch['*(ix|ux)'] = false; // doesn't work on unix/linux
 *
 * // for regular OS deps
 * $reg->dependencies['required']->os['windows'] = true; // only works on windows
 *
 * // for conflicting OS deps
 * $reg->dependencies['required']->os['freebsd'] = false; // doesn't work on FreeBSD
 *
 * // dependency group setup
 * $group = $reg->dependencies['group']->groupname;
 * $group->hint = 'Install optional stuff as a group';
 *
 * $group->package['channel/PackageName1']->save();
 * $group->package['channel/PackageName2']->min('1.2.0');
 * $group->subpackage['channel/PackageName3']->min('1.3.0');
 *
 * $group->extension['extension']->save();
 * </code>
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\PackageFile\v2;
class Dependencies implements \ArrayAccess
{
    protected $parent;
    protected $info = array();
    protected $deptype = null;

    function __construct($parent, $info, $deptype = null)
    {
        $this->parent = $parent;
        $this->info = $info;
        $this->deptype = $deptype;
    }

    function getPackageFile()
    {
        if ($this->parent instanceof self) {
            return $this->parent->getPackageFile();
        }

        return $this->parent;
    }

    function __get($var)
    {
        if ($var === 'parent') {
            if ($this->parent) {
                if ($this->parent instanceof self) {
                    return $this->parent->parent;
                }

                return $this->parent;
            } else {
                return null;
            }
        }

        if ($this->deptype === null) {
            throw new Dependencies\Exception(
                'Cannot retrieve dependency type, choose $pf->dependencies[\'required\']->' .
                $var . ' or $pf->dependencies[\'optional\']->' . $var);
        }

        $depname = 'name';
        if (!isset($this->info[$var])) {
            switch ($var) {
                case 'arch' :
                    $depname = 'pattern';
                    // break intentionally omitted
                case 'os' :
                    if ($this->deptype === 'optional' || $this->deptype === 'group') {
                        throw new Dependencies\Exception( $var . ' dependency is not supported as an optional dependency');
                    }
                    $info = array();
                    break;
                case 'php' :
                    $info = array('min' => null, 'max' => null, 'exclude' => null);
                    break;
                case 'pearinstaller' :
                    $info = array('min' => null, 'max' => null, 'exclude' => null, 'recommended' => null);
                    break;
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                    $info = array();
                    return new Dependencies\Package($this->deptype, $var, $this, $info);
                default :
                    throw new Dependencies\Exception('Unknown dependency type: '. $var);
            }
        } else {
            $info = $this->info[$var];
            if (!is_array($info)) {
                $info = array($info);
            }

            $keys = array();
            switch ($var) {
                case 'php' :
                    $keys = array('min' => null, 'max' => null, 'exclude' => null);
                    break;
                case 'pearinstaller' :
                    $keys = array('min' => null, 'max' => null, 'exclude' => null, 'recommended' => null);
                    break;
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                    if (count($info) && !isset($info[0])) {
                        $info = array($info);
                    }

                    return new Dependencies\Package($this->deptype, $var, $this, $info);
            }

            foreach ($keys as $key => $null) {
                if (!array_key_exists($key, $info)) {
                    $info[$key] = $null;
                }
            }
        }

        return new Dependencies\Dep($this, $info, $var);
    }

    function __set($var, $value)
    {
        if ($this->deptype === null) {
            throw new Dependencies\Exception(
                'Cannot set dependency type, choose $pf->dependencies[\'required\']->' .
                $var . ' or $pf->dependencies[\'optional\']->' . $var);
        }

        if ($value === null) {
            unset($this->info[$var]);
            $this->save();
            return;
        }

        $info = array();
        switch ($var) {
            case 'php' :
                if (is_string($value)) {
                    $info['min'] = $value;
                }
                break;
            case 'pearinstaller' :
                if (is_string($value)) {
                    $info['min'] = $value;
                }
                break;
            case 'arch' :
            case 'os' :
                if ($this->deptype === 'optional' || $this->deptype === 'group') {
                    throw new Dependencies\Exception(
                            $var . ' dependency is not supported as an optional dependency');
                }

                if (!($value instanceof Dependencies\Dep)) {
                    throw new Dependencies\Exception(
                        'Can only set ' . $var . ' to \PEAR2\Pyrus\PackageFile\v2\Dependencies\Dep object'
                    );
                }

                $this->info[$var] = $value->getInfo();
                $this->save();
                return;
            case 'package' :
            case 'subpackage' :
            case 'extension' :
                if (!($value instanceof Dependencies\Package)) {
                    throw new Dependencies\Exception(
                        'Can only set ' . $var . ' to \PEAR2\Pyrus\PackageFile\v2\Dependencies\Package object'
                    );
                }

                $this->info[$var] = $value->getInfo();
                $this->save();
                return;
            default :
                throw new Dependencies\Exception('Unknown dependency type: ' . $var);
        }

        $this->info[$var] = $info;
        $this->save();
    }

    function __isset($var)
    {
        if ($this->deptype === null) {
            return false;
        }

        return isset($this->info[$var]) && !empty($this->info[$var]);
    }

    function offsetGet($var)
    {
        if ($this->deptype !== null) {
            throw new Dependencies\Exception('Cannot access ' .
                                    '$pf->dependencies[\'' . $this->deptype . '\'][\'' . $var . '\']');
        }

        if ($var === 'required' || $var === 'optional') {
            if (!isset($this->info[$var]) || !is_array($this->info[$var])) {
                $this->info[$var] = array();
            }

            return new Dependencies($this, $this->info[$var], $var);
        }

        if ($var === 'group') {
            if (!isset($this->info[$var]) || !is_array($this->info[$var])) {
                $this->info[$var] = array();
            }

            return new Dependencies\Group($this, $this->info['group']);
        }
    }

    function offsetSet($var, $value)
    {
        if ($this->deptype !== null) {
            throw new Dependencies\Exception('Cannot set ' .
                                    '$pf->dependencies[\'' . $this->deptype . '\'][\'' . $var . '\']');
        }

        if ($var === 'group') {
            if (!($value instanceof Dependencies\Group)) {
                throw new Dependencies\Exception('Cannot set group to anything' .
                            ' but a \PEAR2\Pyrus\PackageFile\v2\Dependencies\Group object');
            }

            $this->info['group'] = $value->getInfo();
            $this->save();
            return;
        }

        if ($var === 'required' || $var === 'optional') {
            if (!($value instanceof Dependencies)) {
                throw new Dependencies\Exception('Cannot set ' . $var . ' to anything' .
                            ' but a \PEAR2\Pyrus\PackageFile\v2\Dependencies object');
            }

            $this->info[$var] = $value->getInfo();
            $this->save();
            return;
        }

        throw new Dependencies\Exception('Only required, optional, or group indices' .
                        ' are supported, was passed ' . $var);
    }

    function offsetExists($var)
    {
        return isset($this->info[$var]) && !empty($this->info[$var]);
    }

    function offsetUnset($var)
    {
        unset($this->info[$var]);
        $this->save();
    }

    function setInfo($deptype, $info)
    {
        foreach ($info as $key => $null) {
            if ($null === null) {
                unset($info[$key]);
            }
        }

        if (!count($info)) {
            unset($this->info[$deptype]);
            return;
        }

        $this->info[$deptype] = $info;
    }

    function getInfo()
    {
        return $this->info;
    }

    function save()
    {
        if ($this->deptype !== null) {
            $this->parent->setInfo($this->deptype, $this->info);
            return $this->parent->save();
        }

        $this->parent->rawdependencies = $this->info;
    }
}