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
    static protected $allRegistries = array();
    /**
     * The parent registry
     *
     * This is used to implement cascading registries
     * @var PEAR2_Pyrus_Registry
     */
    protected $parent;

    protected $registries = array();
    /**
     * The channel registry for this path
     *
     * @var PEAR2_PyruschannelRegistry
     */
    protected $channelRegistry;

    public function setChannelRegistry(PEAR2_PyruschannelRegistry $reg)
    {
        $this->channelRegistry = $reg;
    }

    public function setParent(PEAR2_Pyrus_Registry $parent = null)
    {
        $this->parent = $parent;
    }

    protected function __construct($path, $registries = array('Sqlite', 'Xml'))
    {
        if (!isset($this->channelRegistry)) {
            $this->channelRegistry = PEAR2_Pyrus_ChannelRegistry::singleton($path,
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
                $this->registries[] = new $registry($path);
            } catch (Exception $e) {
                $exceptions[] = $e;
            }
        }
        if (!count($this->registries)) {
            throw new PEAR2_Pyrus_Registry_Exception(
                'Unable to initialize registry for path "' . $path . '"',
                $exceptions);
        }
    }

    /**
     * @param string $path
     * @param array $registries
     * @return PEAR2_Pyrus_Registry
     */
    static public function singleton($path, $registries = array('Sqlite', 'Xml'))
    {
        if (!isset(self::$allRegistries[$path])) {
            self::$allRegistries[$path] = new PEAR2_Pyrus_Registry($path);
        }
        return self::$allRegistries[$path];
    }

    public function install(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        foreach ($this->registries as $reg) {
            $reg->install($info);
        }
    }

    public function uninstall($name, $channel)
    {
        foreach ($this->registries as $reg) {
            $reg->uninstall($name, $channel);
        }
    }

    /**
     * Determines whether a package exists
     *
     * @param string $package
     * @param string $channel
     * @param bool $onlyMain if true, only check the primary registry
     * @return unknown
     */
    public function exists($package, $channel, $onlyMain = false)
    {
        $ret = $this->registries[0]->exists($package, $channel);
        if ($onlyMain) {
            return $ret;
        }
        if (!$ret) {
            if (!$this->parent) {
                return false;
            }
            return $this->parent->exists($package, $channel);
        }
        return true;
    }

    public function info($package, $channel, $field, $onlyMain = false)
    {
        if ($onlyMain) {
            return $this->registries[0]->info($package, $channel, $field);
        }
        if ($this->exists($package, $channel, true)) {
            return $this->registries[0]->info($package, $channel, $field);
        }
        if ($this->exists($package, $channel, false)) {
            if (!$this->parent) {
                return null;
            }
            // installed in parent registry
            return $this->parent->info($package, $channel, $field);
        }
    }

    public function listPackages($channel, $onlyMain = false)
    {
        $ret = $this->registries[0]->listPackages($channel);
        if ($onlyMain) {
            return $ret;
        }
        if ($this->parent) {
            return array_merge($ret, $this->parent->listPackages($channel));
        } else {
            return $ret;
        }
    }

    // TODO: fix to support cascading
    public function getIterator()
    {
        return $this->registries[0];
    }

    public function toPackageFile($package, $channel, $onlyMain = false)
    {
        if ($this->exists($package, $channel, true)) {
            foreach ($this->registries as $reg) {
                if ($reg instanceof PEAR2_Pyrus_Registry_Xml) {
                	// prefer xml for retrieving packagefile object
                	try {
                	    return $reg->toPackageFile($package, $channel);
                	} catch (Exception $e) {
                	    // failed, cascade to using default registry instead
                	}
                }
            }
            return $this->registries[0]->toPackageFile($package, $channel);
        }
        if ($onlyMain) {
            return null;
        }
        if ($this->exists($package, $channel, false)) {
            if (!$this->parent) {
                return null;
            }
            // installed in parent registry
            return $this->parent->toPackageFile($package, $channel);
        }
    }

    function __get($var)
    {
        // first registry is always the primary registry
        if ($var == 'package') {
            return $this->registries[0]->package;
        }
        if ($var == 'channel') {
            return $this->channelRegistry;
        }
        if ($var == 'registries') {
            return $this->registries;
        }
    }
}