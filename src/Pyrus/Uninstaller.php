<?php
/**
 * \pear2\Pyrus\Uninstaller
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
 * Pyrus Uninstaller class
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus;
class Uninstaller{
    /**
     * Flag that determines the behavior of {@link begin()}
     *
     * If true, begin() will do nothing.  If false, then
     * {@link self::$installPackages} will be reset to an empty array
     * @var bool
     */
    protected static $inTransaction = false;
    /**
     * Packages that will be uninstalled
     *
     * This list is used when {@link commit()} is called to determine
     * the packages to install
     * @var array
     */
    protected static $uninstallPackages = array();

    /**
     * Packages that were uninstalled
     *
     * This list is used when {@link rollback()} is called to determine
     * the packages that should be restored
     * @var array
     */
    protected static $uninstalledPackages = array();

    /**
     * Packages that have been installed and also successfully registered as uninstalled
     *
     * This list is used when {@link rollback()} is called to determine
     * the packages that should be removed from the registry
     * @var array
     */
    protected static $registeredPackages = array();

    protected static $lastCurrent;

    /**
     * Prepare uninstallation of packages
     */
    static function begin()
    {
        if (!self::$inTransaction) {
            if (isset(\pear2\Pyrus\Main::$options['install-plugins'])) {
                self::$lastCurrent = \pear2\Pyrus\Config::current();
                \pear2\Pyrus\Config::setCurrent(\pear2\Pyrus\Config::current()->plugins_dir);
            }
            self::$uninstallPackages = array();
            self::$uninstalledPackages = array();
            self::$inTransaction = true;
        }
    }

    /**
     * Add a package to the list of packages to be removed
     *
     * This function checks to see if an identical package is already being downloaded,
     * and manages removing duplicates or erroring out on a conflict
     * @param \pear2\Pyrus\Package $package
     */
    static function prepare($packageName)
    {
        try {
            $package = \pear2\Pyrus\Config::current()->registry->package[$packageName];
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\Uninstaller\Exception('Invalid package name ' .
                                                        $packageName, $e);
        }
        if (isset(self::$uninstallPackages[$package->channel . '/' . $package->name])) {
            return;
        }
        self::$uninstallPackages[$package->channel . '/' . $package->name] = $package;
        return $package;
    }

    /**
     * Cancel installation
     */
    static function rollback()
    {
        if (self::$inTransaction) {
            self::$inTransaction = false;
            self::$uninstallPackages = array();
            self::$uninstalledPackages = array();
            self::$registeredPackages = array();
            if (isset(\pear2\Pyrus\Main::$options['install-plugins'])) {
                \pear2\Pyrus\Config::setCurrent(self::$lastCurrent->path);
            }
        }
    }

    /**
     * Install packages slated for installation during transaction
     */
    static function commit()
    {
        if (!self::$inTransaction) {
            return false;
        }
        $installer = new \pear2\Pyrus\Uninstaller;
        // validate dependencies
        $errs = new \pear2\MultiErrors;
        $reg = \pear2\Pyrus\Config::current()->registry;
        try {
            foreach (self::$uninstallPackages as $package) {
                $package->validateUninstallDependencies(self::$uninstallPackages, $errs);
            }
            if (count($errs->E_ERROR)) {
                throw new \pear2\Pyrus\Installer\Exception('Dependency validation failed ' .
                    'for some installed packages, installation aborted', $errs);
            }
            // create dependency connections and load them into the directed graph
            $graph = new \pear2\Pyrus\DirectedGraph;
            foreach (self::$uninstallPackages as $package) {
                $package->makeUninstallConnections($graph, self::$uninstallPackages);
            }
            // topologically sort packages and install them via iterating over the graph
            $actual = array();
            foreach ($graph as $package) {
                $actual[] = $package;
            }
            // easy reverse topological sort
            array_reverse($actual);

            \pear2\Pyrus\AtomicFileTransaction::begin();
            $reg->begin();
            try {
                foreach ($actual as $package) {
                    $installer->uninstall($package, $reg);
                    self::$uninstalledPackages[] = $package;
                }
                $dirtrees = array();
                foreach (self::$uninstalledPackages as $package) {
                    $dirtrees[] = $reg->info($package->name, $package->channel, 'dirtree');
                    $previous = $reg->toPackageFile($package->name, $package->channel, true);
                    self::$registeredPackages[] = array($package, $previous);
                    $reg->uninstall($package->name, $package->channel);
                }

                \pear2\Pyrus\AtomicFileTransaction::rmEmptyDirs($dirtrees);
                \pear2\Pyrus\AtomicFileTransaction::commit();
                $reg->commit();
                \pear2\Pyrus\AtomicFileTransaction::removeBackups();
            } catch (\Exception $e) {
                if (\pear2\Pyrus\AtomicFileTransaction::inTransaction()) {
                    \pear2\Pyrus\AtomicFileTransaction::rollback();
                }
                $reg->rollback();
                throw $e;
            }
            self::$uninstallPackages = array();
            \pear2\Pyrus\Config::current()->saveConfig();
            if (isset(\pear2\Pyrus\Main::$options['install-plugins'])) {
                \pear2\Pyrus\Config::setCurrent(self::$lastCurrent->path);
            }
        } catch (\Exception $e) {
            self::rollback();
            throw $e;
        }
    }

    /**
     * Uninstall a package
     *
     * Remove files
     * @param \pear2\Pyrus\Package $package
     */
    function uninstall(\pear2\Pyrus\PackageFileInterface $package, \pear2\Pyrus\RegistryInterface $reg)
    {
        if (!empty($this->_options['register-only'])) {
            // pretty much nothing happens if we are only registering the install
            return;
        }
        try {
            $config = new \pear2\Pyrus\Config\Snapshot($package->date . ' ' . $package->time);
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\Installer\Exception('Cannot retrieve files, config ' .
                                    'snapshot could not be processed', $e);
        }
        $configpaths = array();
        foreach (\pear2\Pyrus\Installer\Role::getValidRoles($package->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roleobj =
                \pear2\Pyrus\Installer\Role::factory($package->getPackageType(), $role);
            $configpaths[$role] = $config->{$roleobj->getLocationConfig()};
        }
        $ret = array();
        foreach ($reg->info($package->name, $package->channel, 'installedfiles') as $file) {
            $transact = \pear2\Pyrus\AtomicFileTransaction::getTransactionObject($file['configpath']);
            $transact->removePath($file['relativepath']);
        }
    }
}
