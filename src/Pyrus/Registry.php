<?php
/**
 * PEAR2_Pyrus_Registry
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
 * Registry manager
 *
 * The registry for PEAR2 consists of four related components
 *
 *  - an sqlite database
 *  - saved original package.xml for each installed package
 *  - saved original channel.xml for each discovered channel
 *  - configuration values at package installation time
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry implements PEAR2_Pyrus_IRegistry, IteratorAggregate
{
    static protected $allRegistries = array();
    /**
     * Class to instantiate for singleton.
     *
     * This is useful for unit-testing and for extending the registry
     * @var string
     */
    static public $className = 'PEAR2_Pyrus_Registry';
    /**
     * The parent registry
     *
     * This is used to implement cascading registries
     * @var PEAR2_Pyrus_Registry
     */
    protected $parent;

    /**
     * The base path of this registry
     *
     * @var string
     */
    protected $path;
    /**
     * If true, this registry is a cascaded parent registry, and should be treated
     * as read-only.
     *
     * @var bool
     */
    protected $readonly;

    protected $registries = array();
    /**
     * The channel registry for this path
     *
     * @var PEAR2_PyruschannelRegistry
     */
    protected $channelRegistry;

    public function setChannelRegistry(PEAR2_Pyrus_ChannelRegistry $reg)
    {
        $this->channelRegistry = $reg;
    }

    public function setParent(PEAR2_Pyrus_Registry $parent = null)
    {
        $this->parent = $parent;
    }

    public function __construct($path, $registries = array('Sqlite3', 'Xml'), $readonly = false)
    {
        $this->path     = $path;
        $this->readonly = $readonly;
        $exceptions     = array();
        foreach ($registries as $registry) {
            try {
                $registry = ucfirst($registry);
                $registry = "PEAR2_Pyrus_Registry_$registry";
                if (!class_exists($registry, true)) {
                    $exceptions[] = new PEAR2_Pyrus_Registry_Exception(
                        'Unknown registry type: ' . $registry);
                    continue;
                }
                $this->registries[] = new $registry($path, $readonly);
            } catch (Exception $e) {
                $exceptions[] = $e;
            }
        }

        if (!count($this->registries)) {
            throw new PEAR2_Pyrus_Registry_Exception(
                'Unable to initialize registry for path "' . $path . '"',
                $exceptions);
        }

        $this->channelRegistry = new PEAR2_Pyrus_ChannelRegistry($path,
            $registries, $readonly);
    }

    public function install(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot install packages, registry is read-only');
        }

        foreach ($this->registries as $reg) {
            $reg->install($info);
        }
    }

    public function uninstall($name, $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot uninstall packages, registry is read-only');
        }

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

        return null;
    }

    public function listPackages($channel, $onlyMain = false)
    {
        $ret = $this->registries[0]->listPackages($channel);
        if ($onlyMain) {
            return $ret;
        }

        if ($this->parent) {
            return array_merge($ret, $this->parent->listPackages($channel));
        }

        return $ret;
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

    public function getParent()
    {
        return $this->parent;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getDependentPackages(PEAR2_Pyrus_Registry_Base $package)
    {
        return $this->_registries[0]->getDependentPackages($package);
    }
}
