<?php
/**
 * PEAR2_Pyrus_PackageFile_v2_Dependencies
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
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile_v2_Dependencies implements ArrayAccess
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

    function __get($var)
    {
        if ($this->deptype === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                'Cannot retrieve dependency type, choose $pf->dependencies[\'required\']->' .
                $var . ' or $pf->dependencies[\'optional\']->' . $var);
        }
        if (!isset($this->info[$var])) {
            switch ($var) {
                case 'arch' :
                case 'os' :
                    if (!isset($this->info[$var])) {
                        $this->info[$var] = array();
                    }
                    break;
                case 'php' :
                    $this->info[$var] = array('min' => null, 'max' => null, 'exclude' => null);
                    break;
                case 'pearinstaller' :
                    $this->info[$var] = array('min' => null, 'max' => null, 'exclude' => null, 'recommended' => null);
                    break;
                case 'package' :
                case 'subpackage' :
                case 'extension' :
                    $this->info[$var] = array();
                    return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package($var, $this, $this->info[$var]);
                case 'group' :
                    break;
            }
        } else {
            if (!is_array($this->info[$var])) {
                $this->info[$var] = array($this->info[$var]);
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
                    if (count($this->info[$var]) && !isset($this->info[$var][0])) {
                        $this->info[$var] = array($this->info[$var]);
                    }
                    return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package($var, $this, $this->info[$var]);
                case 'group' :
                    break;
            }
            foreach ($keys as $key => $null) {
                if (!array_key_exists($key, $this->info[$var])) {
                    $this->info[$var][$key] = $null;
                }
            }
        }
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Dep($this, $this->info[$var], $var);
    }

    function __set($var, $value)
    {
        if ($this->deptype === null) {
            throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
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
                if (is_string($value)) {
                    $info['pattern'] = $value;
                }
                break;
            case 'os' :
                if (is_string($value)) {
                    $info['name'] = $value;
                }
                break;
            case 'package' :
            case 'subpackage' :
            case 'extension' :
                if (!($value instanceof PEAR2_Pyrus_PackageFile_v2_Dependencies_Package)) {
                    throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception(
                        'Can only set package to PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object'
                    );
                }
                $this->info[$var] = $value->getInfo();
                $this->save();
                return;
                break;
            default :
                throw new PEAR2_Pyrus_PackageFile_v2_Dependencies_Exception('unknown dependency type ' . $var);
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
        if ($var === 'required' || $var === 'optional') {
            if (!isset($this->info[$var]) || !is_array($this->info[$var])) {
                $this->info[$var] = array();
            }
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies($this, $this->info[$var], $var);
        }
        if ($var === 'group') {
            if (!isset($this->info[$var]) || !is_array($this->info[$var])) {
                $this->info[$var] = array();
            }
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Group($this, $this->info['group']);
        }
    }

    function offsetSet($var, $value)
    {
        
    }

    function offsetExists($var)
    {
        return isset($this->info[$var]) && !empty($this->info[$var]);
    }

    function offsetUnset($var)
    {
        unset($this->info[$var]);
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

    function save()
    {
        if ($this->deptype !== null) {
            $this->parent->setInfo($this->deptype, $this->info);
            return $this->parent->save();
        }
        $this->parent->rawdependencies = $this->info;
    }
}