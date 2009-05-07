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
     * Installer options.  Valid indices are:
     *
     * - ignore_errors
     * @var array
     */
    public static $options = array();
    /**
     * Prepare uninstallation of packages
     */
    static function begin()
    {
        if (!self::$inTransaction) {
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
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepare($packageName)
    {
        try {
            $package = PEAR2_Pyrus_Config::current()->registry->package[$packageName];
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Uninstaller_Exception('Invalid package name ' .
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
            self::$uninstallPackages = array();
            self::$uninstalledPackages = array();
            self::$registeredPackages = array();
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
            foreach (self::$uninstallPackages as $package) {
                $package->validateUninstallDependencies(self::$uninstallPackages, $errs);
            }
            if (count($errs->E_ERROR)) {
                throw new PEAR2_Pyrus_Installer_Exception('Dependency validation failed ' .
                    'for some installed packages, installation aborted', $errs);
            }
            // create dependency connections and load them into the directed graph
            $graph = new PEAR2_Pyrus_DirectedGraph;
            foreach (self::$uninstallPackages as $package) {
                $package->makeUninstallConnections($graph, self::$uninstallPackages);
            }
            // topologically sort packages and install them via iterating over the graph
            PEAR2_Pyrus_AtomicFileTransaction::begin();
            $actual = array();
            foreach ($graph as $package) {
                $actual[] = $package;
            }
            $reg = PEAR2_Pyrus_Config::current()->registry;
            // easy reverse topological sort
            array_reverse($actual);
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
            PEAR2_Pyrus_AtomicFileTransaction::rmEmptyDirs($dirtrees);
            PEAR2_Pyrus_AtomicFileTransaction::commit();
            PEAR2_Pyrus_AtomicFileTransaction::removeBackups();
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
     * Remove files
     * @param PEAR2_Pyrus_Package $package
     */
    function uninstall(PEAR2_Pyrus_IPackageFile $package, PEAR2_Pyrus_IRegistry $reg)
    {
        if (!empty($this->_options['register-only'])) {
            // pretty much nothing happens if we are only registering the install
            return;
        }
        try {
            $config = new PEAR2_Pyrus_Config_Snapshot($package->date . ' ' . $package->time);
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Installer_Exception('Cannot retrieve files, config ' .
                                    'snapshot could not be processed', $e);
        }
        $configpaths = array();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($package->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roleobj =
                PEAR2_Pyrus_Installer_Role::factory($package->getPackageType(), $role);
            $configpaths[$role] = $config->{$roleobj->getLocationConfig()};
        }
        $ret = array();
        foreach ($reg->info($package->name, $package->channel, 'installedfiles') as $file) {
            $transact = PEAR2_Pyrus_AtomicFileTransaction::getTransactionObject($file['configpath']);
            $transact->removePath($file['relativepath']);
        }
    }
}
