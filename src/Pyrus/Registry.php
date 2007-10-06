<?php
/**
 * Registry manager
 *
 * The registry for PEAR2 consists of four related components
 * 
 *  - an sqlite database
 *  - saved original package.xml for each installed package
 *  - saved original channel.xml for each discovered channel
 *  - configuration values at package installation time
 */
class PEAR2_Pyrus_Registry implements PEAR2_Pyrus_IRegistry, IteratorAggregate
{
    static private $_allRegistries = array();
    private $_registries = array();
    /**
     * The channel registry
     *
     * @var PEAR2_Pyrus_ChannelRegistry
     */
    static private $_channelRegistry;

    public static function setChannelRegistry(PEAR2_Pyrus_ChannelRegistry $reg)
    {
        self::$_channelRegistry = $reg;
    }

    protected function __construct($path, $registries = array('Sqlite', 'Xml'))
    {
        if (!isset(self::$_channelRegistry)) {
            self::$_channelRegistry = PEAR2_Pyrus_ChannelRegistry::singleton($path,
                $registries);
        }
        $exceptions = array();
        foreach ($registries as $registry) {
            try {
                $registry = ucfirst($registry);
                $registry = "PEAR2_Pyrus_Registry_$registry";
                if (!class_exists($registry, true)) {
                    $exceptions[] = new PEAR2_Pyrus_Registry_Exception(
                        'Unknown registry type: ' . $registry);
                    continue;
                }
                $this->_registries[] = new $registry($path);
            } catch (Exception $e) {
                $exceptions[] = $e;
            }
        }
        if (!count($this->_registries)) {
            throw new PEAR2_Pyrus_Registry_Exception(
                'Unable to initialize registry for path "' . $path . '"',
                $exceptions);
        }
    }

    static public function singleton($path, $registries = array('Sqlite', 'Xml'))
    {
        if (!isset(self::$_allRegistries[$path])) {
            self::$_allRegistries[$path] = new PEAR2_Pyrus_Registry($path);
        }
        return self::$_allRegistries[$path];
    }

    public function install(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        foreach ($this->_registries as $reg) {
            $reg->install($info);
        }
    }

    public function upgrade(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        foreach ($this->_registries as $reg) {
            $reg->upgrade($info);
        }
    }

    public function uninstall($name, $channel)
    {
        foreach ($this->_registries as $reg) {
            $reg->uninstall($name, $channel);
        }
    }

    public function exists($package, $channel)
    {
        return $this->_registries[0]->exists($package, $channel);
    }

    public function info($package, $channel, $field)
    {
        return $this->_registries[0]->info($package, $channel, $field);
    }

    public function listPackages($channel)
    {
        return $this->_registries[0]->listPackages($channel);
    }

    public function getIterator()
    {
        return $this->_registries[0];
    }

    function __get($var)
    {
        // first registry is always the primary registry
        if ($var == 'package') {
            return $this->_registries[0]->package;
        }
        if ($var == 'channel') {
            return self::$_channelRegistry;
        }
        if ($var == 'registries') {
            return $this->_registries;
        }
    }
}