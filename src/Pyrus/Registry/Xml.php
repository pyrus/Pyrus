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
class PEAR2_Pyrus_Registry_Xml extends PEAR2_Pyrus_Registry_Base
{
    protected $readonly;
    private $_path;

    function __construct($path, $readonly = false)
    {
        $this->_path = $path;
        $this->readonly = $readonly;
    }

    private function _nameRegistryPath(PEAR2_Pyrus_IPackageFile $info = null,
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
     * @param PEAR2_Pyrus_IPackageFile $pf
     */
    function install(PEAR2_Pyrus_IPackageFile $info, $replace = false)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot install package, registry is read-only');
        }
        // remove previously installed version for upgrade
        $this->uninstall($info->name, $info->channel);
        $packagefile = $this->_nameRegistryPath($info);
        if (!@is_dir(dirname($packagefile))) {
            mkdir(dirname($packagefile), 0777, true);
        }

        if (!$replace) {
            $info->date = date('Y-m-d');
            $info->time = date('H:i:s');
        }
        foreach ($info->files as $name => $file) {
            unset($file->{'install-as'});
        }
        $arr = $info->toArray();
        file_put_contents($packagefile, (string) new PEAR2_Pyrus_XMLWriter($arr));
    }

    function uninstall($package, $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot install package, registry is read-only');
        }
        if (!$this->exists($package, $channel)) {
            return;
        }
        $packagefile = glob($this->_namePath($channel, $package) .
            DIRECTORY_SEPARATOR . '*.xml');
        if (!$packagefile || !isset($packagefile[0])) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot find registry for package ' .
                $channel . '/' . $package);
        }
        unlink($packagefile[0]);
        rmdir(dirname($packagefile[0]));
    }

    public function exists($package, $channel)
    {
        $packagefile = $this->_namePath($channel, $package);
        return @file_exists($packagefile) && @is_dir($packagefile);
    }

    public function info($package, $channel, $field)
    {
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel .
                '/' . $package);
        }
        $packagefile = glob($this->_namePath($channel, $package) .
            DIRECTORY_SEPARATOR . '*.xml');
        if (!$packagefile || !isset($packagefile[0])) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot find registry for package ' .
                $channel . '/' . $package);
        }
        
        $packageobject = new PEAR2_Pyrus_Package($packagefile[0]);

        if ($field === null) {
            return $packageobject->getInternalPackage()->getPackageFile()->getPackageFileObject();
        }

        if ($field == 'version') {
            $field = 'release-version';
        } elseif ($field == 'installedfiles') {
            $ret = array();
            try {
                $config = new PEAR2_Pyrus_Config_Snapshot($packageobject->date . ' ' . $packageobject->time);
            } catch (Exception $e) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve files, config ' .
                                        'snapshot could not be processed', $e);
            }
            $roles = array();
            foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($packageobject->getPackageType()) as $role) {
                // set up a list of file role => configuration variable
                // for storing in the registry
                $roles[$role] =
                    PEAR2_Pyrus_Installer_Role::factory($packageobject->getPackageType(), $role);
            }
            $ret = array();
            foreach ($packageobject->installcontents as $file) {
                $relativepath = $roles[$file->role]->getRelativeLocation($packageobject, $file);
                if (!$relativepath) {
                    continue;
                }
                $ret[] = $config->{$roles[$file->role]->getLocationConfig()} .
                    DIRECTORY_SEPARATOR . $relativepath;
            }
            return $ret;
        } elseif ($field == 'dirtree') {
            $files = $this->info($package, $channel, 'installedfiles');
            foreach ($files as $file) {
                do {
                    $file = dirname($file);
                    if (strlen($file) > strlen($this->_path)) {
                        $ret[$file] = 1;
                    }
                } while (strlen($file) > strlen($this->_path));
            }
            $ret = array_keys($ret);
            usort($ret, 'strnatcasecmp');
            return array_reverse($ret);
        }

        return $packageobject->$field;
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
                if ($file->isDot()) {
                    continue;
                }
                try {
                    foreach (new DirectoryIterator($file->getPathName()) as $registries) {
                        if ($registries->isDir()) {
                            continue;
                        }
                        $a = $parser->parse($registries->getPathName());
                        $ret[] = $a['package']['name'];
                    }
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
                'for package ' . $channel . '/' . $package . ', it is not installed');
        }
        $packagefile = $this->info($package, $channel, null);
        
        return $packagefile;
    }

    public function __get($var)
    {
        if ($var == 'package') {
            return new PEAR2_Pyrus_Registry_Xml_Package($this);
        }
    }

    /**
     * Don't even try - sqlite is the only one that can reliably implement this
     */
    public function getDependentPackages(PEAR2_Pyrus_Registry_Base $package)
    {
        return array();
    }
}
