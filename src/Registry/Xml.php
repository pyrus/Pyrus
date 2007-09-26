<?php
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
        $packagefile = $this->_nameRegistryPath($info);
        if (!@is_dir(dirname($packagefile))) {
            mkdir(dirname($packagefile), 0777, true);
        }
        file_put_contents($packagefile, (string) $info);
    }

    function upgrade(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        $packagefile = $this->_nameRegistryPath($info);
        @unlink($packagefile);
        $this->installPackage($info);
    }

    function uninstall($package, $channel)
    {
        $packagefile = $this->_nameRegistryPath($info);
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
        return $pf->packageInfo($field);
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