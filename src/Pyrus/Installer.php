<?php
/**
 * PEAR2_Pyrus_Installer
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
 * Pyrus Installer class
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Installer
{
    /**
     * Flag that determines the behavior of {@link begin()}
     *
     * If true, begin() will do nothing.  If false, then
     * {@link static::$installPackages} will be reset to an empty array
     * @var bool
     */
    protected static $inTransaction = false;

    /**
     * Packages that will be installed
     *
     * This list is used when {@link commit()} is called to determine
     * the packages to install
     * @var array
     */
    protected static $installPackages = array();

    /**
     * Packages that were installed
     *
     * This list is used when {@link rollback()} is called to determine
     * the packages that should be removed
     * @var array
     */
    protected static $installedPackages = array();

    /**
     * Packages that have been installed and also successfully registered as installed
     *
     * This list is used when {@link rollback()} is called to determine
     * the packages that should be removed from the registry
     * @var array
     */
    protected static $registeredPackages = array();

    /**
     * Packages that were removed during installation
     *
     * This list is used when {@link rollback()} is called to restore state
     * the packages to install
     * @var array
     */
    protected static $removedPackages = array();
    
    protected static $phase = 1;

    /**
     * Installer options.  Valid indices are:
     *
     * - upgrade (upgrade or install packages)
     * - optionaldeps (also automatically download/install optional deps)
     * - force
     * @var array
     */
    public static $options = array();
    /**
     * Prepare installation of packages
     */
    static function begin()
    {
        if (!static::$inTransaction) {
            static::$installPackages = array();
            static::$installedPackages = array();
            static::$removedPackages = array();
            static::$inTransaction = true;
            static::$phase = 1;
        }
    }

    /**
     * Add a package to the list of packages to be downloaded
     *
     * This function checks to see if an identical package is already being downloaded,
     * and manages removing duplicates or erroring out on a conflict
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepare(PEAR2_Pyrus_IPackage $package, $isdep = false)
    {
        if (!isset(static::$installPackages[$package->channel . '/' . $package->name])) {
            // checking of validity for upgrade is done by PEAR2_Pyrus_Package_Dependency::retrieve(),
            // so all deps that make it this far can be added
            if (!$isdep && PEAR2_Pyrus_Config::current()->registry->exists(
                  $package->name, $package->channel)) {
                if (version_compare($package->version['release'],
                      PEAR2_Pyrus_Config::current()->registry->info(
                        $package->name, $package->channel, 'version'), '<=')) {
                    // installed package is the same or newer version than this one
                    if (!isset(static::$options['force'])) {
                        PEAR2_Pyrus_Log::log(1, 'Skipping installed package ' .
                            $package->channel . '/' . $package->name);
                        return;
                    }
                }
            }
            static::$installPackages[$package->channel . '/' . $package->name] = $package;
            return;
        }
        $clone = static::$installPackages[$package->channel . '/' . $package->name];
        if ($package->isStatic() && !$clone->isStatic()) {
            // always prefer explicitly versioned over abstract
            static::$installPackages[$package->channel . '/' . $package->name] = $package;
            return;
        }
        if (!$package->isStatic() && !$clone->isStatic()) {
            // identical, ignore this package
            return;
        }
        // compare version
        if ($package->isStatic() && $clone->isStatic() && $package->version['release'] === $clone->version['release']) {
            // identical, ignore this package
            return;
        }
        if (!static::$options['force']) {
            //
            static::rollback();
            throw new PEAR2_Pyrus_Installer_Exception('Cannot install ' .
                $package->channel . '/' . $package->name . ', two conflicting' .
                ' versions are required by packages that depend on it (' .
                $package->version['release'] . ' and ' . $clone->version['release']);
        }
        PEAR2_Pyrus_Log::log(0, 'Warning: two conflicting versions of ' .
            $package->channel . '/' . $package->name .
            ' are required by packages that depend on it (' .
            $package->version['release'] . ' and ' . $clone->version['release']);
    }

    /**
     * Download and prepare all dependencies
     *
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepareDependencies(PEAR2_Pyrus_IPackage $package)
    {
        foreach (array('package', 'subpackage') as $p) {
            foreach ($package->dependencies['required']->$p as $dep) {
                if ($dep->conflicts) {
                    continue;
                }
                PEAR2_Pyrus_Package_Dependency::retrieve(get_called_class(), static::$installPackages, $dep, $package);
            }
        }
        if ($package->requestedGroup) {
            foreach (array('package', 'subpackage') as $p) {
                foreach ($package->dependencies['group']->{$package->requestedGroup}->$p as $dep) {
                    if ($dep->conflicts) {
                        continue;
                    }
                    PEAR2_Pyrus_Package_Dependency::retrieve(get_called_class(), static::$installPackages, $dep, $package);
                }
            }
        }
        if (!isset(static::$options['optionaldeps'])) {
            return;
        }
        foreach (array('package', 'subpackage') as $p) {
            foreach ($package->dependencies['optional']->$p as $dep) {
                if ($dep->conflicts) {
                    continue;
                }
                PEAR2_Pyrus_Package_Dependency::retrieve(get_called_class(), static::$installPackages, $dep, $package);
            }
        }
    }

    /**
     * Cancel installation
     */
    static function rollback()
    {
        if (static::$inTransaction) {
            static::$inTransaction = false;
            $reg = PEAR2_Pyrus_Config::current()->registry;
            $err = new PEAR2_MultiErrors;
            foreach (static::$registeredPackages as $package) {
                try {
                    $reg->uninstall($package[0]->name, $package[0]->channel);
                    if ($package[1]) {
                        $reg->install($package[1]);
                    }
                } catch (Exception $e) {
                    $err->E_ERROR[] = $e;
                }
            }
            foreach (static::$removedPackages as $package) {
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
            static::$installPackages = array();
            static::$installedPackages = array();
            static::$registeredPackages = array();
            static::$removedPackages = array();
            if (count($err)) {
                throw new PEAR2_Pyrus_Installer_Exception('Could not successfully rollback', $err);
            }
        }
    }

    /**
     * Prior to committing, ensure all dependencies are resolved properly.
     *
     * This is split off from commit() solely for unit testing purposes
     */
    static function preCommitDependencyResolve()
    {
        if (!static::$inTransaction) {
            return false;
        }
        if (!count(static::$installPackages)) {
            return;
        }
        $done = true;
        $allpackages = $packages = static::$installPackages;
        do {
            foreach ($allpackages as $package => $info) {
                if (!$info->isRemote()) {
                    // anything downloaded is good
                    continue;
                }
                $dep = PEAR2_Pyrus_Package_Dependency::getCompositeDependency($info);
                try {
                    if (true === $info->figureOutBestVersion($dep)) {
                        // we just changed version from a previously calculated version,
                        // so restart
                        $unset = PEAR2_Pyrus_Package_Dependency::removePackage($info);
                        foreach ($unset as $p) {
                            unset(static::$installPackages[$p]);
                        }
                        // just added some new packages that affect dependencies
                        $done = false;
                        continue 2;
                    }
                    static::prepareDependencies($info);
                } catch (PEAR2_Pyrus_Channel_Exception $e) {
                    throw new PEAR2_Pyrus_Installer_Exception('Dependency validation failed ' .
                        'for some packages to install, installation aborted', $e);
                }
            }
            $packages = array();
            foreach (array_diff(array_keys(static::$installPackages), array_keys($allpackages)) as $package) {
                $packages[$package] = static::$installPackages[$package];
            }
            $allpackages = static::$installPackages;
            $done = !count($packages);
        } while (!$done);
        if (!isset(self::$options['force'])) {
            // now iterate over the list and remove any packages that are installed with this version
            $packages = static::$installPackages;
            $reg = PEAR2_Pyrus_Config::current()->registry;
            foreach ($packages as $key => $package) {
                if ($reg->info($package->name, $package->channel, 'version') === $package->version['release']) {
                    unset(static::$installPackages[$key]);
                }
            }
        }
    }

    /**
     * Install packages slated for installation during transaction
     */
    static function commit()
    {
        if (!static::$inTransaction) {
            return false;
        }
        try {
            static::preCommitDependencyResolve();
            $installer = new PEAR2_Pyrus_Installer;
            // validate dependencies
            $errs = new PEAR2_MultiErrors;
            foreach (static::$installPackages as $package) {
                $package->validateDependencies(static::$installPackages, $errs);
            }
            if (count($errs->E_ERROR)) {
                throw new PEAR2_Pyrus_Installer_Exception('Dependency validation failed ' .
                    'for some packages to install, installation aborted', $errs);
            }
            // download non-local packages
            foreach (static::$installPackages as $package) {
                $package->download();
            }

            // now validate everything to the fine-grained level
            foreach (static::$installPackages as $package) {
                $package->validate(PEAR2_Pyrus_Validate::INSTALLING);
            }

            // create dependency connections and load them into the directed graph
            $graph = new PEAR2_Pyrus_DirectedGraph;
            foreach (static::$installPackages as $package) {
                $package->makeConnections($graph, static::$installPackages);
            }
            // topologically sort packages and install them via iterating over the graph
            try {
                PEAR2_Pyrus_AtomicFileTransaction::begin();
                foreach ($graph as $package) {
                    $installer->install($package);
                    static::$installedPackages[] = $package;
                }
                PEAR2_Pyrus_AtomicFileTransaction::commit();
            } catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
                PEAR2_Pyrus_AtomicFileTransaction::rollback();
                throw new PEAR2_Pyrus_Installer_Exception('Installation of ' . $package->channel .
                                                          '/' . $package->name . ' failed', $e);
            }
            $reg = PEAR2_Pyrus_Config::current()->registry;
            foreach (static::$installedPackages as $package) {
                try {
                    $previous = $reg->toPackageFile($package->name, $package->channel, true);
                } catch (\Exception $e) {
                    $previous = null;
                }
                static::$registeredPackages[] = array($package, $previous);
                $reg->install($package->getPackageFile()->info);
            }
            static::$installPackages = array();
            PEAR2_Pyrus_Config::current()->saveConfig();
            // success
            PEAR2_Pyrus_AtomicFileTransaction::removeBackups();
            static::$inTransaction = false;
        } catch (Exception $e) {
            static::rollback();
            throw $e;
        }
    }

    /**
     * Install a fully downloaded package
     *
     * Using PEAR2_Pyrus_FileTransactions and the woPEAR2_Pyrus_PEAR2_Installer_Role* to
     * group files in appropriate locations, the install() method then passes
     * on the registration of installation to PEAR2_Pyrus_Registry.  If necessary,
     * PEAR2_Pyrus_Config will update the install-time snapshots of configuration
     * @param PEAR2_Pyrus_Package $package
     */
    function install(PEAR2_Pyrus_IPackage $package)
    {
        $this->_options = array();
        try {
            $lastversion = PEAR2_Pyrus_Config::current()->registry->info(
                                    $package->name, $package->channel, 'version');
        } catch (Exception $e) {
            $lastversion = null;
        }
        $globalreplace = array('attribs' =>
                    array('from' => '@' . 'PACKAGE_VERSION@',
                          'to' => 'version',
                          'type' => 'package-info'));

        foreach ($package->installcontents as $file) {
            $channel = $package->channel;
            // {{{ assemble the destination paths
            if (!in_array($file->role,
                  PEAR2_Pyrus_Installer_Role::getValidRoles($package->getPackageType()))) {
                throw new PEAR2_Pyrus_Installer_Exception('Invalid role `' .
                        $file->role .
                        "' for file " . $file->name);
            }
            $role = PEAR2_Pyrus_Installer_Role::factory($package->getPackageType(), $file->role);
            $role->setup($this, $package, $file['attribs'], $file->name);
            if (!$role->isInstallable()) {
                continue;
            }

            $transact = PEAR2_Pyrus_AtomicFileTransaction::getTransactionObject($role);

            $info = $role->getRelativeLocation($package, $file, true);
            $dir = $info[0];
            $dest_file = $info[1];

            // }}}

            // pretty much nothing happens if we are only registering the install
            if (isset($this->_options['register-only'])) {
                continue;
            }
            try {
                $transact->mkdir($dir, 0755);
            } catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
                throw new PEAR2_Pyrus_Installer_Exception("failed to mkdir $dir", $e);
            }
            PEAR2_Pyrus_Log::log(3, "+ mkdir $dir");

            if ($file->md5sum) {
                $md5sum = md5_file($package->getFilePath($file->packagedname));
                if (strtolower($md5sum) == strtolower($file->md5sum)) {
                    PEAR2_Pyrus_Log::log(2, "md5sum ok: $dest_file");
                } else {
                    if (empty($options['force'])) {
                        if (!isset($options['ignore-errors'])) {
                            throw new PEAR2_Pyrus_Installer_Exception(
                                "bad md5sum for file $file");
                        } else {
                            if (!isset($options['soft'])) {
                                PEAR2_Pyrus_Log::log(0,
                                    "warning : bad md5sum for file $dest_file");
                            }
                        }
                    } else {
                        if (!isset($options['soft'])) {
                            PEAR2_Pyrus_Log::log(0,
                                "warning : bad md5sum for file $dest_file");
                        }
                    }
                }
            }

            if (strpos(PHP_OS, 'WIN') === false) {
                if ($role->isExecutable()) {
                    $mode = (~octdec(PEAR2_Pyrus_Config::current()->umask) & 0777);
                    PEAR2_Pyrus_Log::log(3, "+ chmod +x $dest_file");
                } else {
                    $mode = (~octdec(PEAR2_Pyrus_Config::current()->umask) & 0666);
                }
            } else {
                $mode = null;
            }

            try {
                $transact->createOrOpenPath($dest_file, $package->getFileContents($file->packagedname, true), $mode);
            } catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
                throw new PEAR2_Pyrus_Installer_Exception(
                    "failed writing to $dest_file", $e);
            }

            $tasks = $file->tasks;
            // only add the global replace task if it is not preprocessed
            if ($package->isNewPackage() && !$package->isPreProcessed()) {
                if (isset($tasks['tasks:replace'])) {
                    if (isset($tasks['tasks:replace'][0])) {
                        $tasks['tasks:replace'][] = $globalreplace;
                    } else {
                        $tasks['tasks:replace'] = array($tasks['tasks:replace'],
                            $globalreplace);
                    }
                } else {
                    $tasks['tasks:replace'] = $globalreplace;
                }
            }
            $fp = false;
            foreach (new PEAR2_Pyrus_Package_Creator_TaskIterator($tasks, $package,
                                                                  PEAR2_Pyrus_Task_Common::INSTALL, $lastversion)
                      as $name => $task) {
                if (!$fp) {
                    $fp = $transact->openPath($dest_file);
                }
                $task->startSession($package, $fp, $dest_file);
                if (!rewind($fp)) {
                    throw new PEAR2_Pyrus_Installer_Exception('task ' . $name .
                                                              ' closed the file pointer, invalid task');
                }
            }
            if ($fp) {
                fclose($fp);
            }
        }
    }

    /**
     * Return an array containing all of the states that are more stable than
     * or equal to the passed in state
     *
     * @param string Release state
     * @param boolean Determines whether to include $state in the list
     * @return false|array False if $state is not a valid release state
     */
    static function betterStates($state, $include = false)
    {
        static $states = array('snapshot', 'devel', 'alpha', 'beta', 'stable');
        $i = array_search($state, $states);
        if ($i === false) {
            return false;
        }
        if ($include) {
            $i--;
        }
        return array_slice($states, $i + 1);
    }
}
