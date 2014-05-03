<?php
/**
 * \Pyrus\Installer
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

namespace Pyrus;

/**
 * Pyrus Installer class
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class Installer
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
     * Packages that were previously installed during an upgrade
     *
     * This list is used by the cfg role to check for previous packages'
     * configuration files
     * @var array
     */
    protected static $wasInstalled = array();

    /**
     * Optional dependencies that won't be installed
     *
     * Informational list of optional depenndencies that
     * will not be installed without the --optionaldeps flag
     * @var array
     */
    protected static $optionalDeps = array();

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

    protected static $lastCurrent;

    /**
     * Prepare installation of packages
     */
    static function begin()
    {
        if (static::$inTransaction === true) {
            return;
        }

        if (isset(Main::$options['install-plugins'])) {
            self::$lastCurrent = Config::current();
            Config::setCurrent(Config::current()->plugins_dir);
        }

        static::$installPackages   = array();
        static::$installedPackages = array();
        static::$removedPackages   = array();
        static::$wasInstalled      = array();
        static::$inTransaction     = true;
        if (isset(Main::$options['packagingroot'])) {
            Config::current()->resetForPackagingRoot();
        }
    }

    /**
     * Add a package to the list of packages to be downloaded
     *
     * This function checks to see if an identical package is already being downloaded,
     * and manages removing duplicates or erroring out on a conflict
     * @param \Pyrus\Package $package
     */
    static function prepare(PackageInterface $package)
    {
        $fullPackageName = $package->channel . '/' . $package->name ;
        if ($package->isPlugin()) {
            if (!isset(Main::$options['install-plugins'])) {

                // Check the plugin registry so we can provide more info
                $command = 'install';
                if (Config::current()->pluginregistry->exists($package->name, $package->channel)) {
                    $command = 'upgrade';
                }
                Logger::log(0, 'Skipping plugin ' . $fullPackageName . 
                               PHP_EOL . 'Plugins modify the installer and cannot be installed at the same time as regular packages.' .
                               PHP_EOL . 'Add the -p option to manage plugins, for example:' .
                               PHP_EOL . ' php pyrus.phar ' . $command . ' -p ' . $fullPackageName);
                return;
            }
        }

        if (!isset(static::$installPackages[$fullPackageName])) {
            // checking of validity for upgrade is done by \Pyrus\Package\Dependency::retrieve(),
            // so all deps that make it this far can be added
            if (Config::current()->registry->exists($package->name, $package->channel)) {
                if (!$package->isUpgradeable() && !isset(Main::$options['force'])) {
                    // installed package is the same or newer version than this one
                    Logger::log(1, 'Skipping installed package ' . $fullPackageName);
                    return;
                }
            }

            static::$installPackages[$fullPackageName] = $package;
            return;
        }

        $clone = static::$installPackages[$fullPackageName];
        if (!$package->isStatic() && $clone->isStatic()) {
            // always prefer explicitly versioned over abstract
            return;
        }

        if ($package->isStatic() && !$clone->isStatic()) {
            // always prefer explicitly versioned over abstract
            static::$installPackages[$fullPackageName] = $package;
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

        static::rollback();
        throw new Installer\Exception('Cannot install ' .
            $fullPackageName . ', two conflicting' .
            ' versions were requested (' .
            $package->version['release'] . ' and ' . $clone->version['release'] . ')');
    }

    static function getIgnoredOptionalDeps()
    {
        return static::$optionalDeps;
    }

    /**
     * Cancel installation
     */
    static function rollback()
    {
        if (static::$inTransaction === false) {
            return;
        }

        static::$inTransaction      = false;
        static::$installPackages    = array();
        static::$wasInstalled       = array();
        static::$installedPackages  = array();
        static::$registeredPackages = array();
        static::$removedPackages    = array();
        if (isset(Main::$options['install-plugins'])) {
            Config::setCurrent(self::$lastCurrent->path);
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

        try {
            $set = new Package\Dependency\Set(static::$installPackages);
            $set->synchronizeDeps();
        } catch (Package\Dependency\Set\Exception $e) {
            throw new Installer\Exception(
                    'Dependency validation failed for some packages to install, installation aborted', $e);
        }

        static::$installPackages = $set->retrieveAllPackages();
        static::$optionalDeps = $set->getIgnoredOptionalDeps();
    }

    function wasInstalled($package, $channel)
    {
        if (isset(static::$wasInstalled[$channel . '/' . $package])) {
            return static::$wasInstalled[$channel . '/' . $package];
        }

        return false;
    }

    /**
     * Install packages slated for installation during transaction
     */
    static function commit()
    {
        if (static::$inTransaction === false) {
            return false;
        }

        try {
            static::preCommitDependencyResolve();
            $installer = new Installer;
            // validate dependencies
            $errs = new \PEAR2\MultiErrors;
            foreach (static::$installPackages as $package) {
                $package->validateDependencies(static::$installPackages, $errs);
            }

            if (count($errs->E_ERROR)) {
                throw new Installer\Exception('Dependency validation failed ' .
                    'for some packages to install, installation aborted', $errs);
            }

            // download non-local packages
            foreach (static::$installPackages as $package) {
                $fullPackageName = $package->channel . '/' . $package->name;
                Logger::log(1, 'Downloading '.$fullPackageName);
                $package->download();
                if ($package->isPlugin()) {
                    // check for downloaded packages
                    if (!isset(Main::$options['install-plugins'])) {

                        // Check the plugin registry so we can provide more info
                        $command = 'install';
                        if (Config::current()->pluginregistry->exists($package->name, $package->channel)) {
                            $command = 'upgrade';
                        }
                        Logger::log(0, 'Skipping plugin ' . $fullPackageName . 
                                       PHP_EOL . 'Plugins modify the installer and cannot be installed at the same time as regular packages.' .
                                       PHP_EOL . 'Add the -p option to manage plugins, for example:' .
                                       PHP_EOL . ' php pyrus.phar ' . $command . ' -p ' . $fullPackageName);
                        unset(static::$installPackages[$fullPackageName]);
                    }
                }
            }

            // now validate everything to the fine-grained level
            foreach (static::$installPackages as $package) {
                $package->validate(Validate::INSTALLING);
            }

            // create dependency connections and load them into the directed graph
            $graph = new DirectedGraph;
            foreach (static::$installPackages as $package) {
                $package->makeConnections($graph, static::$installPackages);
            }

            // topologically sort packages and install them via iterating over the graph
            $packages = $graph->topologicalSort();
            $reg = Config::current()->registry;
            try {
                AtomicFileTransaction::begin();
                $reg->begin();
                if (isset(Main::$options['upgrade'])) {
                    foreach ($packages as $package) {
                        $fullPackageName = $package->channel . '/' . $package->name;
                        if ($reg->exists($package->name, $package->channel)) {
                            static::$wasInstalled[$fullPackageName] = $reg->package[$fullPackageName];
                            $reg->uninstall($package->name, $package->channel);
                        }
                    }
                }

                static::detectDownloadConflicts($packages, $reg);
                foreach ($packages as $package) {
                    if (isset(static::$installedPackages[$package->channel . '/' . $package->name])) {
                        continue;
                    }

                    $installer->install($package);
                    static::$installedPackages[$package->channel . '/' . $package->name] = $package;
                }

                foreach (static::$installedPackages as $package) {
                    try {
                        $previous = $reg->toPackageFile($package->name, $package->channel, true);
                    } catch (\Exception $e) {
                        $previous = null;
                    }

                    $reg->install($package->getPackageFile()->info);
                    static::$registeredPackages[] = array($package, $previous);
                }

                static::$installPackages = array();
                AtomicFileTransaction::commit();
                $reg->commit();
            } catch (AtomicFileTransaction\Exception $e) {
                $errs = new \PEAR2\MultiErrors();
                $errs->E_ERROR[] = $e;
                try {
                    AtomicFileTransaction::rollback();
                } catch (\Exception $ex) {
                    $errs->E_WARNING[] = $ex;
                }
                try {
                    $reg->rollback();
                } catch (\Exception $ex) {
                    $errs->E_WARNING[] = $ex;
                }
                throw new Installer\Exception('Installation failed', $errs);
            }

            Config::current()->saveConfig();
            // success
            AtomicFileTransaction::removeBackups();
            static::$inTransaction = false;
            if (isset(Main::$options['install-plugins'])) {
                Config::setCurrent(self::$lastCurrent->path);
            }
        } catch (\Exception $e) {
            static::rollback();
            throw $e;
        }
    }

    static protected function detectDownloadConflicts($packages, $reg)
    {
        // check conflicts with packages already installed
        $checked = $conflicts = array();
        foreach ($packages as $package) {
            $fullPackageName = $package->channel . '/' . $package->name;
            if (isset($checked[$fullPackageName])) {
                continue;
            }

            $checked[$fullPackageName] = 1;
            $conflict = $reg->detectFileConflicts($package);
            if (!count($conflict)) {
                continue;
            }

            $conflicts[$fullPackageName] = $conflict;
        }

        // check conflicts with other downloaded packages
        $dupes = $checked = $filelist = array();
        foreach ($packages as $package) {
            $fullPackageName = $package->channel . '/' . $package->name;
            if (isset($checked[$fullPackageName])) {
                continue;
            }

            $checked[$fullPackageName] = 1;
            foreach ($package->installcontents as $file) {
                // TODO: Ignore file conflicts when force is applied?
                // TODO: Add more error checking i think
                $role = Installer\Role::factory($package->getPackageType(), $file->role);
                list(,$path) = $role->getRelativeLocation($package, $file, true);

                if (isset($filelist[$file->role][$path])) {
                    $dupes[$path] = $file->role;
                }

                $filelist[$file->role][$path][] = $fullPackageName;
            }
        }

        foreach ($dupes as $path => $role) {
            $conflicted = array_shift($filelist[$role][$path]);
            foreach ($filelist[$role][$path] as $package) {
                $conflicts[$conflicted][] = array($path => $package);
            }
        }

        if (count($conflicts)) {
            $message = "File conflicts detected:\n";
            foreach ($conflicts as $package => $files) {
                $message .= " Package $package:\n";
                foreach ($files as $info) {
                    foreach ($info as $path => $conflict) {
                        $message .= '  ' . $path . ' (conflicts with package ' . $conflict . ")\n";
                    }
                }
            }

            AtomicFileTransaction::rollback();
            throw new Installer\Exception($message);
        }
    }

    static function getInstalledPackages()
    {
        return static::$installedPackages;
    }

    /**
     * Install a fully downloaded package
     *
     * Using \Pyrus\FileTransactions and the Pyrus\Installer\Role* to
     * group files in appropriate locations, the install() method then passes
     * on the registration of installation to \Pyrus\Registry.  If necessary,
     * Config will update the install-time snapshots of configuration
     * @param \Pyrus\Package $package
     */
    function install(PackageInterface $package)
    {
        $this->_options = array();
        $lastversion = Config::current()->registry->info(
                                $package->name, $package->channel, 'version');
        $globalreplace = array('attribs' =>
                    array('from' => '@' . 'PACKAGE_VERSION@',
                          'to' => 'version',
                          'type' => 'package-info'));

        foreach ($package->installcontents as $file) {
            $channel = $package->channel;
            // {{{ assemble the destination paths
            $roles = Installer\Role::getValidRoles($package->getPackageType());
            if (!in_array($file->role, $roles)) {
                throw new Installer\Exception('Invalid role `' .
                        $file->role .
                        "' for file " . $file->name);
            }

            $role = Installer\Role::factory($package->getPackageType(), $file->role);
            $role->setup($this, $package, $file['attribs'], $file->name);
            if (!$role->isInstallable()) {
                continue;
            }

            $transact  = AtomicFileTransaction::getTransactionObject($role);
            $info      = $role->getRelativeLocation($package, $file, true);
            $dir       = $info[0];
            $dest_file = $info[1];

            // }}}

            // pretty much nothing happens if we are only registering the install
            if (isset($this->_options['register-only'])) {
                continue;
            }

            try {
                $transact->mkdir($dir, 0755);
            } catch (AtomicFileTransaction\Exception $e) {
                throw new Installer\Exception("failed to mkdir $dir", $e);
            }

            Logger::log(3, "+ mkdir $dir");
            if ($file->md5sum) {
                $md5sum = md5_file($package->getFilePath($file->packagedname));
                if (strtolower($md5sum) == strtolower($file->md5sum)) {
                    Logger::log(2, "md5sum ok: $dest_file");
                } else {
                    if (!isset(Main::$options['force'])) {
                        throw new Installer\Exception("bad md5sum for file " . $file->name);
                    }

                    Logger::log(0, "warning : bad md5sum for file " . $file->name);
                }
            } else {
                // installing from package.xml in source control, save the md5 of the current file
                $file->md5sum = md5_file($package->getFilePath($file->packagedname));
            }

            if (strpos(PHP_OS, 'WIN') === false) {
                if ($role->isExecutable()) {
                    $mode = (~octdec(Config::current()->umask) & 0777);
                    Logger::log(3, "+ chmod +x $dest_file");
                } else {
                    $mode = (~octdec(Config::current()->umask) & 0666);
                }
            } else {
                $mode = null;
            }

            try {
                $transact->createOrOpenPath($dest_file, $package->getFileContents($file->packagedname, true), $mode);
            } catch (AtomicFileTransaction\Exception $e) {
                throw new Installer\Exception("failed writing to $dest_file", $e);
            }

            $tasks = $file->tasks;
            // only add the global replace task if it is not preprocessed
            if ($package->isNewPackage() && !$package->isPreProcessed()) {
                if (isset($tasks['tasks:replace'])) {
                    if (isset($tasks['tasks:replace'][0])) {
                        $tasks['tasks:replace'][] = $globalreplace;
                    } else {
                        $tasks['tasks:replace'] = array($tasks['tasks:replace'], $globalreplace);
                    }
                } else {
                    $tasks['tasks:replace'] = $globalreplace;
                }
            }

            $fp = false;
            foreach (new Package\Creator\TaskIterator($tasks, $package,
                                                      Task\Common::INSTALL, $lastversion)
                      as $name => $task) {
                if (!$fp) {
                    $fp = $transact->openPath($dest_file);
                }

                $task->startSession($fp, $dest_file);
                if (!rewind($fp)) {
                    throw new Installer\Exception('task ' . $name . ' closed the file pointer, invalid task');
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
