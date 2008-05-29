<?php
/**
 * PEAR2_Pyrus_Uninstaller
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
class PEAR2_Pyrus_Uninstaller
{
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

    /**
     * Packages that were restored during uninstallation
     *
     * This list is used when {@link rollback()} is called to restore state
     * the packages to install
     * @var array
     */
    protected static $restoredPackages = array();

    /**
     * Installer options.  Valid indices are:
     *
     * - ignore_errors
     * @var array
     */
    public static $options = array();
    static protected $transact;
    /**
     * Prepare uninstallation of packages
     */
    static function begin()
    {
        if (!self::$inTransaction) {
            self::$transact = new PEAR2_Pyrus_FileTransactions;
            self::$transact->registerTransaction('rmdir', new PEAR2_Pyrus_FileTransactions_Rmdir);
            self::$transact->registerTransaction('rename', new PEAR2_Pyrus_FileTransactions_Rename);
            self::$uninstallPackages = array();
            self::$uninstalledPackages = array();
            self::$restoredPackages = array();
            self::$inTransaction = true;
        }
    }

    /**
     * Add a package to the list of packages to be removed
     *
     * This function checks to see if an identical package is already being downloaded,
     * and manages removing duplicates or erroring out on a conflict
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepare($packageName)
    {
        try {
            $package = PEAR2_Pyrus_Config::current()->registry[$packageName];
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Uninstaller_Exception('Invalid package name ' .
                                                        $packageName, $e);
        }
        if (isset(self::$uninstallPackages[$package->channel . '/' . $package->name])) {
            return;
        }
        self::$uninstallPackages[$package->channel . '/' . $package->name] = $package;
    }

    /**
     * Download and prepare all dependencies
     *
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepareDependencies(PEAR2_Pyrus_Package $package)
    {
        foreach ($package->dependencies->required->package as $dep) {
            if (isset($dep['conflicts'])) {
                continue;
            }
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, false, true));
        }
        foreach ($package->dependencies->required->subpackage as $dep) {
            if (isset($dep['conflicts'])) {
                continue;
            }
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, true, true));
        }
        if ($package->requestedGroup) {
            foreach ($package->dependencies->group[$package->requestedGroup]->package as $dep) {
                self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package));
            }
            foreach ($package->dependencies->group[$package->requestedGroup]->subpackage as $dep) {
                self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, true));
            }
        }
        if (!isset(self::$options['optionaldeps'])) {
            return;
        }
        foreach ($package->dependencies->optional->package as $dep) {
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package));
        }
        foreach ($package->dependencies->optional->subpackage as $dep) {
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, true));
        }
    }

    /**
     * Cancel installation
     */
    static function rollback()
    {
        if (self::$inTransaction) {
            self::$inTransaction = false;
            self::$transact->rollback();
            $reg = PEAR2_Pyrus_Config::current()->registry;
            $err = new PEAR2_MultiErrors;
            foreach (self::$registeredPackages as $package) {
                try {
                    $reg->uninstall($package[0]->name, $package[0]->channel);
                    if ($package[1]) {
                        $reg->install($package[1]);
                    }
                } catch (Exception $e) {
                    $err->E_ERROR[] = $e;
                }
            }
            foreach (self::$removedPackages as $package) {
                try {
                    $reg->uninstall($package->name, $package->channel);
                } catch (Exception $e) {
                    $err->E_ERROR[] = $e;
                }
                try {
                    $reg->install($package->getPackageFile()->info);
                } catch (Exception $e) {
                    $err->E_ERROR[] = $e;
                }
            }
            self::$installPackages = array();
            self::$installedPackages = array();
            self::$registeredPackages = array();
            self::$removedPackages = array();
            if (count($err)) {
                throw new PEAR2_Pyrus_Installer_Exception('Could not successfully rollback', $err);
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
        try {
            $installer = new PEAR2_Pyrus_Uninstaller;
            // validate dependencies
            $errs = new PEAR2_MultiErrors;
            foreach (self::$installPackages as $package) {
                $package->validateUninstallDependencies(self::$uninstallPackages, $errs);
            }
            if (count($errs->E_ERROR)) {
                throw new PEAR2_Pyrus_Installer_Exception('Dependency validation failed ' .
                    'for some installed packages, installation aborted', $errs);
            }
            // create dependency connections and load them into the directed graph
            $graph = new PEAR2_Pyrus_DirectedGraph;
            foreach (self::$uninstallPackages as $package) {
                $package->makeConnections($graph, self::$uninstallPackages);
            }
            // topologically sort packages and install them via iterating over the graph
            self::$transact->begin();
            $actual = array();
            foreach ($graph as $package) {
                $actual[] = $package;
            }
            // easy reverse topological sort
            array_reverse($actual);
            foreach ($actual as $package) {
                $installer->uninstall($package);
                self::$uninstalledPackages[] = $package;
            }
            self::$transact->commit();
            $reg = PEAR2_Pyrus_Config::current()->registry;
            foreach (self::$uninstalledPackages as $package) {
                $previous = $reg->toPackageFile($package->name, $package->channel, true);
                self::$registeredPackages[] = array($package, $previous);
                $reg->uninstall($package->name, $package->channel);
            }
            self::$uninstallPackages = array();
            PEAR2_Pyrus_Config::current()->saveConfig();
        } catch (Exception $e) {
            self::rollback();
            throw $e;
        }
    }

    /**
     * Uninstall a package
     *
     * Using PEAR2_Pyrus_FileTransactions and the woPEAR2_Pyrus_PEAR2_Installer_Role* to
     * group files in appropriate locations, the install() method then passes
     * on the registration of installation to PEAR2_Pyrus_Registry.  If necessary,
     * PEAR2_Pyrus_Config will update the install-time snapshots of configuration
     * @param PEAR2_Pyrus_Package $package
     */
    function uninstall(PEAR2_Pyrus_IRegistry $package)
    {
        foreach ($package->file as $file) {
            if (empty($this->_options['register-only'])) {
                $this->addFileOperation('delete', array($path));
            }
            // pretty much nothing happens if we are only registering the install
        }
        if ($dirtree = $package->getDirTree()) {
            // attempt to delete empty directories
            uksort($dirtree, array($this, '_sortDirs'));
            foreach($dirtree as $dir => $notused) {
                $this->addFileOperation('rmdir', array($dir));
            }
        }
    }
}
