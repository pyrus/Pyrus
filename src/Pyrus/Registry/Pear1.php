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
 * @link      http://svn.pear.php.net/PEAR2/Pyrus
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
 * @link      http://svn.pear.php.net/PEAR2/Pyrus
 */
class PEAR2_Pyrus_Registry_Pear1 extends PEAR2_Pyrus_Registry_Base
{
    static public $dependencyDBClass = 'PEAR2_Pyrus_Registry_Pear1_DependencyDB';
    protected $_path;
    protected $filemap;

    function __construct($path)
    {
        $this->_path = $path;
        $this->filemap = $path . DIRECTORY_SEPARATOR . '.filemap';
    }

    protected function rebuildFileMap()
    {
        $config = PEAR2_Pyrus_Config::current();
        $channels = array();
        foreach ($config->channelregistry as $channel) {
            $channels[$channel->name] = $this->listPackages($channel->name);
        }
        $files = array();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles('php') as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory('php', $role)->getLocationConfig();
        }
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles('extsrc') as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            if (isset($roles[$role])) {
                continue;
            }
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory('extsrc', $role)->getLocationConfig();
        }
        foreach ($channels as $channel => $packages) {
            foreach ($packages as $package) {
                foreach ($this->info($package, $channel, 'installedfiles') as $name => $attrs) {

                    $name = str_replace($config->{$roles[$attrs['role']]}, '', $name);
                    $file = str_replace('\\', '/', $name);

                    $file = preg_replace(',^/+,', '', $file);
                    if (!isset($files[$attrs['role']])) {
                        $files[$attrs['role']] = array();
                    }
                    if ($channel != 'pear.php.net') {
                        $files[$attrs['role']][$file] = array($channel,
                            strtolower($package));
                    } else {
                        $files[$attrs['role']][$file] = strtolower($package);
                    }
                }
            }
        }

        $fp = @fopen($this->filemap, 'wb');
        if (!$fp) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot write out Pear1 filemap');
        }

        fwrite($fp, serialize($files));
        fclose($fp);
    }

    protected function readFileMap()
    {
        if (!file_exists($this->filemap)) {
            return array();
        }

        $fp = @fopen($this->filemap, 'r');
        if (!$fp) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not open Pear1 registry filemap "' . $this->filemap . '"');
        }

        clearstatcache();
        $rt = get_magic_quotes_runtime();
        set_magic_quotes_runtime(0);
        $fsize = filesize($this->filemap);
        $data = stream_get_contents($fp);
        fclose($fp);
        set_magic_quotes_runtime($rt);
        $tmp = unserialize($data);
        if (!$tmp && $fsize > 7) {
            throw new PEAR2_Pyrus_Registry_Exception('Invalid Pear1 registry filemap data');
        }
        return $tmp;
    }

    private function _nameRegistryPath(PEAR2_Pyrus_IPackageFile $info = null,
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
     * @param PEAR2_Pyrus_IPackageFile $pf
     */
    function install(PEAR2_Pyrus_IPackageFile $info, $replace = false)
    {
        $packagefile = $this->_nameRegistryPath($info);
        if (!@is_dir(dirname($packagefile))) {
            mkdir(dirname($packagefile), 0777, true);
        }

        if (!$replace) {
            $info->date = date('Y-m-d');
            $info->time = date('H:i:s');
        }

        $arr = $info->toArray();
        $arr = $arr['package'];
        $arr['old']['version'] = $info->version['release'];
        $arr['old']['release_date'] = $info->date;
        $arr['old']['release_state'] = $info->state;
        $license = $info->license['name'];
        $arr['old']['release_license'] = $license;
        $arr['old']['release_notes'] = $info->notes;
        $deps = array();
        $deps[] = array_merge(array('type' => 'php'), $info->dependencies['required']->php->getInfo());
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
                foreach ($info->dependencies['required']->package as $dep) {
                    $s = array('type' => $map[$dtype]);
                    if (isset($dep->channel)) {
                        $s['channel'] = $dep->channel;
                    }
                    if (isset($dep->uri)) {
                        $s['uri'] = $dep->uri;
                    }
                    if (isset($dep->name)) {
                        $s['name'] = $dep->name;
                    }
                    if ($dep->conflicts) {
                        $s['rel'] = 'not';
                    } else {
                        if (!isset($dep->min) &&
                              !isset($dep->max)) {
                            $s['rel'] = 'has';
                            $s['optional'] = $optional;
                        } elseif (isset($dep->min) &&
                              isset($dep->max)) {
                            $s['rel'] = 'ge';
                            $s1 = $s;
                            $s1['rel'] = 'le';
                            $s['version'] = $dep->min;
                            $s1['version'] = $dep->max;
                            if (isset($dep->channel)) {
                                $s1['channel'] = $dep->channel;
                            }
                            $s['name'] = $dep->name;
                            $s1['name'] = $dep->name;
                            $s['optional'] = $optional;
                            $s1['optional'] = $optional;
                            $deps[] = $s1;
                        } elseif (isset($dep->min)) {
                            $s['rel'] = 'ge';
                            if (isset($dep->exclude)) {
                                foreach ($dep->exclude as $exclude) {
                                    if ($exclude == $dep->min) {
                                        $s['rel'] = 'gt';
                                        break;
                                    }
                                }
                            }
                            $s['version'] = $dep->min;
                            $s['optional'] = $optional;
                            $s['name'] = $dep->name;
                        } elseif (isset($dep->max)) {
                            $s['rel'] = 'le';
                            if (isset($dep->exclude)) {
                                foreach ($dep->exclude as $exclude) {
                                    if ($exclude == $dep->max) {
                                        $s['rel'] = 'lt';
                                        break;
                                    }
                                }
                            }
                            $s['version'] = $dep->max;
                            $s['optional'] = $optional;
                            $s['name'] = $dep->name;
                        }
                    }
                }
            }
        }
        $arr['old']['release_deps'] = $deps;
        $maintainers = $info->allmaintainers;
        $maint = array();
        foreach (array('lead', 'developer', 'contributor', 'helper') as $role) {
            foreach ($maintainers[$role] as $maintainer) {
                $m = $maintainer->getInfo();
                $m = array_merge(array('role' => $role), $m);
                $m['handle'] = $m['user'];
                unset($m['handle']);
                $maint[] = $m;
            }
        }
        $arr['filelist'] = $info->getFilelist();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($info->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory($info->getPackageType(), $role);
        }
        $config = PEAR2_Pyrus_Config::current();
        $dirtree = array();
        foreach ($info->installcontents as $file) {
            $relativepath = $roles[$file->role]->getRelativeLocation($info, $file);
            if (!$relativepath) {
                continue;
            }
            $arr['filelist'][$file['attribs']['name']] = $arr['filelist'][$file['attribs']['name']]['attribs'];
            $installedas = $config->{$roles[$file->role]->getLocationConfig()} .
                DIRECTORY_SEPARATOR . $relativepath;
            $arr['filelist'][$file['attribs']['name']]['installed_as'] = $installedas;
            $len = strlen($installedas) - strlen($relativepath) - 2;
            do {
                $installedas = dirname($installedas);
                if (strlen($installedas) > $len) {
                    $dirtree[$installedas] = 1;
                }
            } while (strlen($installedas) > $len);
        }
        $arr['filelist']['dirtree'] = array_keys($dirtree);
        $arr['old']['maintainers'] = $maint;
        $arr['xsdversion'] = '2.0';
        $arr['_lastmodified'] = time();

        file_put_contents($packagefile, serialize($arr));
        $this->rebuildFileMap();
        $classname = self::$dependencyDBClass;
        $dep = new $classname;
        $dep->installPackage($info);
    }

    function uninstall($package, $channel)
    {
        $packagefile = $this->_nameRegistryPath(null, $channel, $package);
        @unlink($packagefile);
        $classname = self::$dependencyDBClass;
        $dep = new $classname;
        $dep->uninstallPackage($channel, $package);
        $this->rebuildFileMap();
    }

    public function exists($package, $channel)
    {
        $packagefile = $this->_nameRegistryPath(null, $channel, $package);
        return @file_exists($packagefile) && @!is_dir($packagefile);
    }

    public function info($package, $channel, $field)
    {
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel .
                '/' . $package);
        }

        $pf = $this->toPackageFile($package, $channel);

        if ($field === null) {
            return $pf;
        }

        if ($field == 'version') {
            $field = 'release-version';
        }
        if ($field == 'installedfiles' || $field == 'dirtree') {
            $packagefile = $this->_namePath($channel, $package) . '.reg';
            if (!$packagefile || !isset($packagefile[0])) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot find registry for package ' .
                    $channel . '/' . $package);
            }

            $packagecontents = file_get_contents($packagefile);
            $data = @unserialize($packagecontents);
            if ($data === false) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve package file object ' .
                    'for package ' . $channel . '/' . $package . ', PEAR 1.x registry file might be corrupt!');
            }
            if ($field == 'dirtree') {
                $ret = $data['filelist']['dirtree'];
                usort($ret, 'strnatcasecmp');
                return array_reverse($ret);
            }

            $ret = array();
            foreach ($data['filelist'] as $file) {
                if (!isset($file['installed_as'])) {
                    continue;
                }
                $ret[$file['installed_as']] = $file;
            }
            return $ret;
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
                } elseif ($a !== false && isset($a['package'])) {
                    $ret[] = $a['package'];
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
                'for package ' . $channel . '/' . $package . ', it is not installed');
        }

        $packagefile = $this->_nameRegistryPath(null, $channel, $package);
        if (!$packagefile) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot find registry for package ' .
                $channel . '/' . $package);
        }

        $contents = file_get_contents($packagefile);
        if (!$contents) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot find registry for package ' .
                $channel . '/' . $package);
        }

        $data = @unserialize($contents);
        if ($data === false) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve package file object ' .
                'for package ' . $channel . '/' . $package . ', PEAR 1.x registry file might be corrupt!');
        }

        if (isset($data['xsdversion']) && $data['xsdversion'] == '1.0'
            || !isset($data['attribs'])
            || isset($data['attribs']) && $data['attribs']['version'] == '1.0') {
            // make scrappy minimal package.xml we can use for dependencies/info
            $pf = new PEAR2_Pyrus_PackageFile_v2;
            $pf->package = $data['name'];
            $pf->channel = 'pear.php.net';
            $pf->version['release'] = $pf->version['api'] = $data['release_version'];
            $pf->stability['release'] = $pf->stability['api'] = $data['release_state'];
            $pf->notes = $data['release_notes'];
            foreach ($data['maintainers'] as $maintainter) {
                $pf->maintainers[$maintainer['handle']]->name($maintainer['name'])
                   ->active('yes')->role($maintainer['role'])->email($maintainer['email']);
            }
            // we don't care what the ancient package depends on, really, so make it valid
            // and forget about it
            $pf->dependencies->php['min'] = phpversion();
            $pf->dependencies->pearinstaller['min'] = '1.4.0';
            unset($data['filelist']['dirtree']);
            if (!isset($data['filelist'][0])) {
                $data['filelist'] = array($data['filelist']);
            }
            foreach ($data['filelist'] as $file) {
                $pf->files[$file['name']] = array('attribs' => $file);
            }
        } else {
            // create packagefile v2 here
            $pf = new PEAR2_Pyrus_PackageFile_v2;
            $pf->fromArray(array('package' => $data));
            $contents = $data['contents']['dir']['file'];
            if (!isset($contents[0])) {
                $contents = array($contents);
            }
            foreach ($contents as $file) {
                $pf->files[$file['attribs']['name']] = $file;
            }
        }
        return $pf;
    }

    public function __get($var)
    {
        if ($var == 'package') {
            return new PEAR2_Pyrus_Registry_Pear1_Package($this);
        }
    }

    public function getDependentPackages(PEAR2_Pyrus_IPackageFile $package)
    {
        $class = self::$dependencyDBClass;
        $dep = new $class;
        $ret = $dep->getDependentPackages($package);
        foreach ($ret as $i => $package) {
            $ret[$i] = $this->package[$package['channel'] . '/' . $package['package']];
        }
        return $ret;
    }

    /**
     * Detect any files already installed that would be overwritten by
     * files inside the package represented by $package
     */
    public function detectFileConflicts(PEAR2_Pyrus_IPackageFile $package)
    {
        $filemap = $this->readFileMap();
        if (!$filemap) {
            return array();
        }

        // now iterate over each file in the package, and note all the conflicts
        $roles = array();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($package->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory($package->getPackageType(), $role);
        }
        $ret = array();
        foreach ($package->installcontents as $file) {
            $relativepath = $roles[$file->role]->getRelativeLocation($package, $file);
            if (!$relativepath) {
                continue;
            }
            if (isset($filemap[$file->role][$relativepath])) {
                if (is_array($filemap[$file->role][$relativepath])) {
                    $ret[] = array($relativepath => $filemap[$file->role][$relativepath][0] . '/' .
                        $this->info($filemap[$file->role][$relativepath][1],
                                    $filemap[$file->role][$relativepath][0], 'name'));
                } else {
                    $ret[] = array($relativepath =>
                                   'pear.php.net/' . $this->info($filemap[$file->role][$relativepath],
                                                                 'pear.php.net',
                                                                 'name'));
                }
            }
        }
        return $ret;
    }
}
