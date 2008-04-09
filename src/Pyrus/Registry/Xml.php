<?php
/**
 * PEAR2_Pyrus_Registry_Xml
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
 * This is the central registry, that is used for all installer options,
 * stored in xml files
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Xml implements PEAR2_Pyrus_IRegistry
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
        $package = $info !== null ? $info->name : $package;
        $path = $this->_namePath($channel, $package);
        $version = $info !== null ? $info->version['release'] : $version;
        return $path . DIRECTORY_SEPARATOR . $version . '-package.xml';
    }

    private function _namePath($channel, $package)
    {
        return PEAR2_Pyrus_Config::current()->path . DIRECTORY_SEPARATOR .
            '.registry' . DIRECTORY_SEPARATOR .
            str_replace('/', '!', $channel) . 
            DIRECTORY_SEPARATOR . $package;
    }

    /**
     * Create the Channel!PackageName-Version-package.xml file
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
        file_put_contents($packagefile, (string) $info);
    }

    function uninstall($package, $channel)
    {
        $packagefile = $this->_nameRegistryPath(null, $channel, $package);
        @unlink($packagefile);
        @rmdir(dirname($packagefile));
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
        $packagefile = glob($this->_namePath($package, $channel) .
            DIRECTORY_SEPARATOR . '*.xml');
        if (!$packagefile || !isset($packagefile[0])) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot find registry for package ' .
                $channel . '/' . $package);
        }
        // create packagefile v2 here
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
            $parser = new PEAR2_Pyrus_XMLParser;
            foreach (new DirectoryIterator($dir) as $file) {
                if ($file->isDot()) continue;
                try {
                    $a = $parser->parse($file->getPathName());
                    $ret[] = $a['package']['name'];
                } catch (Exception $e) {
                    PEAR2_Pyrus_Log::log(0, 'Warning: corrupted XML registry entry: ' .
                        $file->getPathName() . ': ' . $e);
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
        $x = new PEAR2_Pyrus_PackageFile($packagefile);
        return $x->info;
    }

    public function __get($var)
    {
        if ($var == 'package') {
            return new PEAR2_Pyrus_Registry_Xml_Package($this);
        }
        if ($var == 'channel') {
            return new PEAR2_Pyrus_Registry_Xml_Channel($this);
        }
    }
}