<?php
/**
 * \Pyrus\Registry
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
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
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
class Registry implements \Pyrus\RegistryInterface, \IteratorAggregate
{
    static protected $allRegistries = array();
    /**
     * Class to instantiate for singleton.
     *
     * This is useful for unit-testing and for extending the registry
     * @var string
     */
    static public $className = 'Pyrus\Registry';
    /**
     * The parent registry
     *
     * This is used to implement cascading registries
     * @var \Pyrus\Registry
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

    public function setChannelRegistry(ChannelRegistry $reg)
    {
        $this->channelRegistry = $reg;
    }

    function getChannelRegistry()
    {
        return $this->channelRegistry;
    }

    public function setParent(Registry $parent = null)
    {
        $this->parent = $parent;
    }

    public function __construct($path, $registries = array('Sqlite3', 'Xml'), $readonly = false)
    {
        $this->path     = $path;
        $this->readonly = $readonly;
        $exceptions     = new \PEAR2\MultiErrors;
        foreach ($registries as $registry) {
            try {
                $registry = ucfirst($registry);
                $registry = 'Pyrus\Registry\\' . $registry;
                if (!class_exists($registry, true)) {
                    $exceptions->E_ERROR[] =
                            new Registry\Exception('Unknown registry type: ' . $registry);
                    continue;
                }

                $this->registries[] = new $registry($path, $readonly);
            } catch (\Exception $e) {
                $exceptions->E_ERROR[] = $e;
            }
        }

        if (!count($this->registries)) {
            throw new Registry\Exception('Unable to initialize registry for path "' . $path . '"',
                                         $exceptions);
        }

        $channelregistry_class = ChannelRegistry::$className;
        $this->channelRegistry = new $channelregistry_class($path, $registries, $readonly);
    }

    public function replace(PackageFileInterface $info)
    {
        if ($this->readonly) {
            throw new Registry\Exception('Cannot install packages, registry is read-only');
        }

        foreach ($this->registries as $reg) {
            $reg->replace($info);
        }
    }

    public function install(PackageFileInterface $info, $replace = false)
    {
        if ($this->readonly) {
            throw new Registry\Exception('Cannot install packages, registry is read-only');
        }

        foreach ($this->registries as $reg) {
            $reg->install($info, $replace);
        }
    }

    public function uninstall($name, $channel)
    {
        if ($this->readonly) {
            throw new Registry\Exception('Cannot uninstall packages, registry is read-only');
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
     * @param bool   $onlyMain if true, only check the primary registry
     *
     * @return bool
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
        $ret = array();
        foreach ($this->registries as $registry) {
            $packages = $registry->listPackages($channel);
            $ret = array_merge($ret, $packages);
        }

        $ret = array_unique($ret);
        if ($onlyMain) {
            return $ret;
        }

        if ($this->parent) {
            return array_unique(array_merge($ret, $this->parent->listPackages($channel)));
        }

        return $ret;
    }

    // TODO: fix to support cascading
    public function getIterator()
    {
        return $this->registries[0];
    }

    /**
     * Get a package object from the registry
     *
     * @return \Pyrus\Package\Installed
     */
    function toPackage($package, $channel)
    {
        $v2         = $this->toPackageFile($package, $channel);
        $file       = new \Pyrus\PackageFile($v2);
        $package    = new \Pyrus\Package\Installed($file, null, $this);
        return $package;
    }

    public function toPackageFile($package, $channel, $onlyMain = false)
    {
        if ($this->exists($package, $channel, true)) {
            foreach ($this->registries as $reg) {
                if ($reg instanceof Registry\Xml) {
                    // prefer xml for retrieving packagefile object
                    try {
                        return $reg->toPackageFile($package, $channel);
                    } catch (\Exception $e) {
                        // failed, cascade to using default registry instead
                        break;
                    }
                }
            }

            return $this->registries[0]->toPackageFile($package, $channel);
        } elseif ($onlyMain || !$this->exists($package, $channel, false)) {
            throw new Registry\Exception('Cannot retrieve package file object ' .
                'for package ' . $channel . '/' . $package . ', it is not installed');
        }

        // installed in parent registry
        return $this->parent->toPackageFile($package, $channel);
    }

    function __get($var)
    {
        // first registry is always the primary registry
        if ($var == 'package') {
            return $this->registries[0]->package;
        }

        if ($var == 'allregistries') {
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

    public function getDependentPackages(PackageFileInterface $package, $minimal = true)
    {
        return $this->registries[0]->getDependentPackages($package, $minimal);
    }

    public function detectFileConflicts(PackageFileInterface $package)
    {
        return $this->registries[0]->detectFileConflicts($package);
    }

    /**
     * Returns a list of registries present in the PEAR installation at $path
     * @param string
     * @return array
     * @todo make it possible to extend the registries to add customized future registries
     */
    static public function detectRegistries($path)
    {
        if (!file_exists($path) || !is_dir($path)) {
            return array();
        }

        return array_merge(Registry\Sqlite3::detectRegistries($path),
                           Registry\Xml::detectRegistries($path),
                           Registry\Pear1::detectRegistries($path));
    }

    /**
     * Completely remove all traces of a registry
     */
    static public function removeRegistry($path, array $registries = array())
    {
        if (!count($registries)) {
            $registries = static::detectRegistries($path);
        }

        foreach ($registries as $reg) {
            $class = 'Pyrus\Registry\\' . ucfirst(strtolower($reg));
            $class::removeRegistry($path);
        }
    }

    function begin()
    {
        foreach ($this->registries as $registry) {
            $registry->begin();
        }
    }

    function rollback()
    {
        foreach ($this->registries as $registry) {
            $registry->rollback();
        }
    }

    function commit()
    {
        foreach ($this->registries as $registry) {
            $registry->commit();
        }
    }
}
