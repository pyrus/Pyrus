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
     * {@link self::$installPackages} will be reset to an empty array
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

    /**
     * Installer options.  Valid indices are:
     *
     * - upgrade (upgrade or install packages)
     * - optionaldeps (also automatically download/install optional deps)
     * @var array
     */
    public static $options = array();
    static protected $transact;
    static protected $installedas;
    /**
     * Prepare installation of packages
     */
    static function begin()
    {
        if (!self::$inTransaction) {
            if (!isset(self::$transact)) {
                self::$transact = new PEAR2_Pyrus_FileTransactions;
                self::$installedas = new PEAR2_Pyrus_FileTransactions_Installedas;
                self::$transact->registerTransaction('installedas', self::$installedas);
                self::$transact->registerTransaction('rmdir', new PEAR2_Pyrus_FileTransactions_Rmdir);
                self::$transact->registerTransaction('rename', new PEAR2_Pyrus_FileTransactions_Rename);
            }
            self::$installPackages = array();
            self::$installedPackages = array();
            self::$removedPackages = array();
            self::$inTransaction = true;
        }
    }

    /**
     * Add a package to the list of packages to be downloaded
     *
     * This function checks to see if an identical package is already being downloaded,
     * and manages removing duplicates or erroring out on a conflict
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepare(PEAR2_Pyrus_Package $package)
    {
        if (!isset(self::$installPackages[$package->channel . '/' . $package->name])) {
            if (PEAR2_Pyrus_Config::current()->registry->exists(
                  $package->name, $package->channel)) {
                if (version_compare($package->version['release'],
                      PEAR2_Pyrus_Config::current()->registry->info(
                        $package->name, $package->channel, 'version'), '<=')) {
                    // installed package is the same or newer version than this one
                    if (!isset(self::$options['force'])) {
                        PEAR2_Pyrus_Log::log(1, 'Skipping installed package ' .
                            $package->channel . '/' . $package->name);
                        return;
                    }
                }
            }
            self::$installPackages[$package->channel . '/' . $package->name] = $package;
            self::prepareDependencies(
                self::$installPackages[$package->channel . '/' . $package->name]);
            return;
        }
        $clone = self::$installPackages[$package->channel . '/' . $package->name];
        // compare version
        if ($package->version['release'] === $clone->version['release']) {
            // identical, ignore this package
            return;
        }
        if (version_compare($package->version['release'], $clone->version['release'], '<')) {
            if ($package->couldBeVersion($clone->version['release'])) {
                // packages depending on the cloned version are OK with a newer version
                // already going to install a newer version of this package, all is OK
                return;
            }
            if (!self::$options['force']) {
                //
                self::rollback();
                throw new PEAR2_Pyrus_Installer_Exception('Cannot install ' .
                    $package->channel . '/' . $package->name . ', two conflicting' .
                    ' versions are required by packages that depend on it (' .
                    $package->version['release'] . ' and ' . $clone->version['release']);
            }
            // ignore this version, it is older
            PEAR2_Pyrus_Log::log(0, 'Warning: two conflicting versions of ' .
                $package->channel . '/' . $package->name .
                ' are required by packages that depend on it (' .
                $package->version['release'] . ' and ' . $clone->version['release']);
            return;
        }
        if ($clone->couldBeVersion($package->version['release'])) {
            // packages depending on the cloned version are OK with this version
            self::$installPackages[$package->channel . '/' . $package->name] = $package;
        } else {
            // the version of $package conflicts with packages depending on this package
            if (!self::$options['force']) {
                self::rollback();
                throw new PEAR2_Pyrus_Installer_Exception('Cannot install ' .
                    $package->channel . '/' . $package->name . ', two conflicting' .
                    ' versions are required by packages that depend on it (' .
                    $package->version['release'] . ' and ' . $clone->version['release']);
            }
            PEAR2_Pyrus_Log::log(0, 'Warning: two conflicting versions of ' .
                $package->channel . '/' . $package->name .
                ' are required by packages that depend on it (' .
                $package->version['release'] . ' and ' . $clone->version['release']);
            self::$installPackages[$package->channel . '/' . $package->name] = $package;
        }
        self::prepareDependencies(
            self::$installPackages[$package->channel . '/' . $package->name]);
    }

    /**
     * Download and prepare all dependencies
     *
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepareDependencies(PEAR2_Pyrus_Package $package)
    {
        foreach ($package->dependencies['required']->package as $dep) {
            if ($dep->conflicts) {
                continue;
            }
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, false, true));
        }
        foreach ($package->dependencies['required']->subpackage as $dep) {
            if ($dep->conflicts) {
                continue;
            }
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, true, true));
        }
        if ($package->requestedGroup) {
            foreach ($package->dependencies['group']->{$package->requestedGroup}->package as $dep) {
                self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package));
            }
            foreach ($package->dependencies['group']->{$package->requestedGroup}->subpackage as $dep) {
                self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, true));
            }
        }
        if (!isset(self::$options['optionaldeps'])) {
            return;
        }
        foreach ($package->dependencies['optional']->package as $dep) {
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package));
        }
        foreach ($package->dependencies['optional']->subpackage as $dep) {
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
            $installer = new PEAR2_Pyrus_Installer;
            // validate dependencies
            $errs = new PEAR2_MultiErrors;
            foreach (self::$installPackages as $package) {
                $package->validateDependencies(self::$installPackages, $errs);
            }
            if (count($errs->E_ERROR)) {
                throw new PEAR2_Pyrus_Installer_Exception('Dependency validation failed ' .
                    'for some installed packages, installation aborted', $errs);
            }
            // download non-local packages
            foreach (self::$installPackages as $package) {
                $package->download();
            }
            // create dependency connections and load them into the directed graph
            $graph = new PEAR2_Pyrus_DirectedGraph;
            foreach (self::$installPackages as $package) {
                $package->makeConnections($graph, self::$installPackages);
            }
            // topologically sort packages and install them via iterating over the graph
            self::$transact->begin();
            foreach ($graph as $package) {
                $installer->install($package);
                self::$installedPackages[] = $package;
            }
            self::$transact->commit();
            $reg = PEAR2_Pyrus_Config::current()->registry;
            foreach (self::$installedPackages as $package) {
                try {
                    $previous = $reg->toPackageFile($package->name, $package->channel, true);
                } catch (\Exception $e) {
                    $previous = null;
                }
                self::$registeredPackages[] = array($package, $previous);
                $reg->install($package->getPackageFile()->info);
            }
            self::$installPackages = array();
            PEAR2_Pyrus_Config::current()->saveConfig();
            // success
            self::$inTransaction = false;
        } catch (Exception $e) {
            self::rollback();
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
    function install(PEAR2_Pyrus_Package $package)
    {
        self::$installedas->reset($package);
        $tmp_path = $package->getLocation();
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

            $info = $role->processInstallation($package, $file, $tmp_path);
            list($save_destdir, $dest_dir, $dest_file, $orig_file) = $info;
            $final_dest_file = $installed_as = $dest_file;

            if (isset($this->_options['packagingroot'])) {
                $final_dest_file = $this->_prependPath($final_dest_file,
                    $this->_options['packagingroot']);
            }
            $dest_dir = dirname($final_dest_file);
            $dest_file = $dest_dir . DIRECTORY_SEPARATOR . '.tmp' .
                basename($final_dest_file);
            // }}}

            if (empty($this->_options['register-only'])) {
                if (!file_exists($dest_dir) || !is_dir($dest_dir)) {
                    if (!mkdir($dest_dir, 0755, true)) {
                        throw new PEAR2_Pyrus_Installer_Exception("failed to mkdir $dest_dir");
                    }
                    PEAR2_Pyrus_Log::log(3, "+ mkdir $dest_dir");
                }
            }
            // pretty much nothing happens if we are only registering the install
            if (empty($this->_options['register-only'])) {
                if (!file_exists($orig_file)) {
                    throw new PEAR2_Pyrus_Installer_Exception("file $orig_file does not exist");
                }
                $contents = file_get_contents($orig_file);
                if ($contents === false) {
                    $contents = '';
                }
                if (isset($file['md5sum'])) {
                    $md5sum = md5($contents);
                }
                $tasks = $file->tasks;
                if ($package->isNewPackage()) {
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
                foreach (new PEAR2_Pyrus_Package_Creator_TaskIterator($tasks, $package)
                          as $task) {
                    if (!$task[1]->isScript()) { // scripts are only handled after installation
                        $task[1]->init($task[0], $file['attribs'], null);
                        $newcontents = $task[1]->startSession($package, $contents, $dest_file);
                        if ($newcontents) {
                            $contents = $newcontents; // save changes
                        }
                    }
                }
                $wp = @fopen($dest_file, "wb");
                if (!is_resource($wp)) {
                    throw new PEAR2_Pyrus_Installer_Exception(
                        "failed to create $dest_file: $php_errormsg");
                }
                if (fwrite($wp, $contents) === false) {
                    throw new PEAR2_Pyrus_Installer_Exception(
                        "failed writing to $dest_file: $php_errormsg");
                }
                fclose($wp);
                // {{{ check the md5
                if (isset($md5sum)) {
                    if (strtolower($md5sum) == strtolower($file['md5sum'])) {
                        PEAR2_Pyrus_Log::log(2, "md5sum ok: $final_dest_file");
                    } else {
                        if (empty($options['force'])) {
                            // delete the file
                            if (file_exists($dest_file)) {
                                unlink($dest_file);
                            }
                            if (!isset($options['ignore-errors'])) {
                                throw new PEAR2_Pyrus_Installer_Exception(
                                    "bad md5sum for file $final_dest_file");
                            } else {
                                if (!isset($options['soft'])) {
                                    PEAR2_Pyrus_Log::log(0,
                                        "warning : bad md5sum for file $final_dest_file");
                                }
                            }
                        } else {
                            if (!isset($options['soft'])) {
                                PEAR2_Pyrus_Log::log(0,
                                    "warning : bad md5sum for file $final_dest_file");
                            }
                        }
                    }
                }
                // }}}
                // {{{ set file permissions
                if (strpos(PHP_OS, 'WIN') === false) {
                    if ($role->isExecutable()) {
                        $mode = (~octdec(PEAR2_Pyrus_Config::current()->umask) & 0777);
                        PEAR2_Pyrus_Log::log(3, "+ chmod +x $dest_file");
                    } else {
                        $mode = (~octdec(PEAR2_Pyrus_Config::current()->umask) & 0666);
                    }
                    self::$transact->chmod($mode, $dest_file);
                    if (!@chmod($dest_file, $mode)) {
                        if (!isset($options['soft'])) {
                            PEAR2_Pyrus_Log::log(0,
                                "failed to change mode of $dest_file: $php_errormsg");
                        }
                    }
                }
                // }}}
                self::$transact->rename($dest_file, $final_dest_file, $role->isExtension());
            }
            // Store the full path where the file was installed for easy uninstall
            self::$transact->installedas($file->name, $installed_as,
                                $save_destdir, dirname(substr($dest_file,
                                 strlen($save_destdir))));
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
