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
class PEAR2_Pyrus_Registry implements PEAR2_Pyrus_IRegistry
{
    static private $_registries = array();
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
            self::$_channelRegistry = new PEAR2_Pyrus_ChannelRegistry($path,
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
                self::$_registries[] = new $registry($path);
            } catch (Exception $e) {
                $exceptions[] = $e;
            }
        }
        if (!count(self::$_registries)) {
            throw new PEAR2_Pyrus_Registry_Exception(
                'Unable to initialize registry for path "' . $path . '"',
                $exceptions);
        }
    }

    static public function singleton($path)
    {
        if (!isset(self::$_registries[$path])) {
            self::$_registries[$path] = new PEAR2_Pyrus_Registry($path);
        }
        return self::$_registries[$path];
    }

    public function install(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        foreach (self::$_registries as $reg) {
            $reg->install($info);
        }
    }

    public function upgrade(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        foreach (self::$_registries as $reg) {
            $reg->upgrade($info);
        }
    }

    public function uninstall($name, $channel)
    {
        foreach (self::$_registries as $reg) {
            $reg->uninstall($name, $channel);
        }
    }

    public function exists($package, $channel)
    {
        return self::$_registries[0]->exists($package, $channel);
    }

    public function info($package, $channel, $field)
    {
        return self::$_registries[0]->info($package, $channel, $field);
    }

    function __get($var)
    {
        // first registry is always the primary registry
        if ($var == 'package') {
            return self::$_registries[0]->package;
        }
        if ($var == 'channel') {
            return self::$_channelRegistry;
        }
        if ($var == 'registries') {
            return self::$_registries;
        }
    }
}