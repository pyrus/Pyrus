<?php
/**
 * PEAR2_Pyrus_Registry_Pear1
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/Pyrus/
 */

/**
 * This is the central registry, that is used for all installer options,
 * stored in .reg files for PEAR 1 compatibility
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Helgi Þormar Þorbjörnsson <dufuz@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/Pyrus/
 */
class PEAR2_Pyrus_Registry_Pear1 implements PEAR2_Pyrus_IRegistry
{
    private $_path;
    function __construct($path)
    {
        $this->_path = $path;
    }

    private function _nameRegistryPath(PEAR2_Pyrus_PackageFile_v2 $info = null,
                                     $channel = null, $package = null, $version = null)
    {
        $channel = $info !== null ? $info->channel : $channel;
        $package = $info !== null ? $info->name    : $package;
        $path = $this->_namePath($channel, $package);
        return $path . '.reg';
    }

    private function _namePath($channel, $package)
    {
        if ($channel == 'pear.php.net') {
            $channel = '';
        } else {
            $channel = '.channel.' . strtolower($channel) . DIRECTORY_SEPARATOR;
        }

        return PEAR2_Pyrus_Config::current()->path . DIRECTORY_SEPARATOR .
            '.registry' . DIRECTORY_SEPARATOR . $channel . strtolower($package);
    }

    /**
     * Create the .registry/package.reg or file
     *
     * @param PEAR2_Pyrus_PackageFile_v2 $pf
     */
    function install(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        // remove previously installed version for upgrade
        $this->uninstall($info->name, $info->channel);
        $packagefile = $this->_nameRegistryPath($info);
        if (!@is_dir(dirname($packagefile))) {
            mkdir(dirname($packagefile), 0777, true);
        }

        $arr = $info->toArray();
        $arr['old']['version'] = $info->version['release'];
        $arr['old']['release_date'] = $info->date;
        $arr['old']['release_state'] = $info->state;
        $license = $info->license;
        if (is_array($license) && isset($license['_content'])) {
            $license = $license['_content'];
        }
        $arr['old']['release_license'] = $license;
        $arr['old']['release_notes'] = $info->notes;
        $deps = array();
        $deps[] = array_merge(array('type' => 'php'), (array) $info->dependencies->php);
        $map = array(
            'php' => 'php',
            'package' => 'pkg',
            'subpackage' => 'pkg',
            'extension' => 'ext',
            'os' => 'os',
            'pearinstaller' => 'pkg',
            );
        foreach (array('package', 'subpackage', 'extension') as $dtype) {
            foreach (array('required', 'optional') as $optorrequired) {
                $optional = ($optorrequired == 'optional');
                foreach ($info->dependencies->required->package as $dep) {
                    $s = array('type' => $map[$dtype]);
                    if (isset($dep['channel'])) {
                        $s['channel'] = $dep['channel'];
                    }
                    if (isset($dep['uri'])) {
                        $s['uri'] = $dep['uri'];
                    }
                    if (isset($dep['name'])) {
                        $s['name'] = $dep['name'];
                    }
                    if (isset($dep['conflicts'])) {
                        $s['rel'] = 'not';
                    } else {
                        if (!isset($dep['min']) &&
                              !isset($dep['max'])) {
                            $s['rel'] = 'has';
                            $s['optional'] = $optional;
                        } elseif (isset($dep['min']) &&
                              isset($dep['max'])) {
                            $s['rel'] = 'ge';
                            $s1 = $s;
                            $s1['rel'] = 'le';
                            $s['version'] = $dep['min'];
                            $s1['version'] = $dep['max'];
                            if (isset($dep['channel'])) {
                                $s1['channel'] = $dep['channel'];
                            }
                            if ($dtype != 'php') {
                                $s['name'] = $dep['name'];
                                $s1['name'] = $dep['name'];
                            }
                            $s['optional'] = $optional;
                            $s1['optional'] = $optional;
                            $deps[] = $s1;
                        } elseif (isset($dep['min'])) {
                            if (isset($dep['exclude']) &&
                                  $dep['exclude'] == $dep['min']) {
                                $s['rel'] = 'gt';
                            } else {
                                $s['rel'] = 'ge';
                            }
                            $s['version'] = $dep['min'];
                            $s['optional'] = $optional;
                            if ($dtype != 'php') {
                                $s['name'] = $dep['name'];
                            }
                        } elseif (isset($dep['max'])) {
                            if (isset($dep['exclude']) &&
                                  $dep['exclude'] == $dep['max']) {
                                $s['rel'] = 'lt';
                            } else {
                                $s['rel'] = 'le';
                            }
                            $s['version'] = $dep['max'];
                            $s['optional'] = $optional;
                            if ($dtype != 'php') {
                                $s['name'] = $dep['name'];
                            }
                        }
                    }
                }
            }
        }
        $arr['old']['release_deps'] = $deps;
        $maintainers = $info->allmaintainers;
        $maint = array();
        foreach (array('lead', 'developer', 'contributor', 'helper') as $role) {
            if ($maintainers[$role]) {
                foreach ($maintainers[$role] as $m) {
                    $m = array_merge(array('role' => $role), $m);
                    $maint[] = $m;
                }
            }
        }
        $arr['old']['maintainers'] = $maint;
        $arr['xsdversion'] = '2.0';
        $arr['_lastmodified'] = time();

        file_put_contents($packagefile, serialize($arr));
    }

    function uninstall($package, $channel)
    {
        $packagefile = $this->_nameRegistryPath(null, $channel, $package);
        @unlink($packagefile);
    }

    public function exists($package, $channel)
    {
        $packagefile = $this->_namePath($package, $channel);
        return @file_exists($packagefile) && @is_dir($packagefile);
    }

    public function info($package, $channel, $field)
    {
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel .
                '/' . $package);
        }

        $packagefile = $this->_namePath($package, $channel) . '.reg';
        if (!$packagefile || !isset($packagefile[0])) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot find registry for package ' .
                $channel . '/' . $package);
        }

        $data = @unserialize($packagefile);
        if ($data === false) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve package file object ' .
                'for package ' . $package . '/' . $channel . ', PEAR 1.x registry file might be corrupt!');
        }

        if (!isset($data['attribs'])
            || (isset($data['attribs']) && $data['attribs']['version'] == '1.0')) {
            // make scrappy minimal package.xml we can use for dependencies/info
        }

        // create packagefile v2 here
        $pf = new PEAR2_Pyrus_PackageFile_v2;
        $pf->fromArray(array('package' => $data));

        if ($field === null) {
            return $pf;
        }

        if ($field == 'version') {
            $field = 'release-version';
        }

        return $pf->$field;
    }

    public function listPackages($channel)
    {
        $dir = $this->_namePath($channel, '');
        if (!@file_exists($dir)) {
            return array();
        }

        $ret = array();
        try {
            foreach (new DirectoryIterator($dir) as $file) {
                if ($file->isDot() && !$file->isFile()) continue;
                $a = @unserialize(file_get_contents($file->getPathName()));
                // $a['name'] is not set on v1 regs
                if ($a !== false && isset($a['name'])) {
                    $ret[] = $a['name'];
                } else {
                    PEAR2_Pyrus_Log::log(0, 'Warning: corrupted REG registry entry: ' .
                        $file->getPathName());
                }
            }
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not open channel directory for ' .
                'channel ' . $channel, $e);
        }
        return $ret;
    }

    public function toPackageFile($package, $channel)
    {
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve package file object ' .
                'for package ' . $package . '/' . $channel . ', it is not installed');
        }
        $packagefile = $this->_nameRegistryPath(null, $channel, $package);
        $data = @unserialize($packagefile);
        if ($data === false) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve package file object ' .
                'for package ' . $package . '/' . $channel . ', REG file might be corrupt!');
        }

        $a = new PEAR2_Pyrus_PackageFile_v2;
        $a->fromArray(array('package' => $data));
        return $a;
    }

    public function __get($var)
    {
        if ($var == 'package') {
            return new PEAR2_Pyrus_Registry_Pear1_Package($this);
        }
    }
}
