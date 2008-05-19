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
                'for package ' . $package . '/' . $channel . ', REG file might be corrupt!');
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
