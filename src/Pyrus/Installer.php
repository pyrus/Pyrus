<?php
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
            self::$transact = new PEAR2_Pyrus_FileTransactions;
            self::$transact->registerTransaction('installedas', self::$installedas);
            self::$transact->registerTransaction('rmdir', new PEAR2_Pyrus_FileTransactions_Rmdir);
            self::$transact->registerTransaction('rename', new PEAR2_Pyrus_FileTransactions_Rename);
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
        if (isset(self::$installPackages[$package->channel . '/' . $package->name])) {
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
    }

    /**
     * Download and prepare all dependencies
     *
     * @param PEAR2_Pyrus_Package $package
     */
    static function prepareDependencies(PEAR2_Pyrus_Package $package)
    {
        foreach ($package->dependencies->required->package as $dep) {
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package));
        }
        foreach ($package->dependencies->required->subpackage as $dep) {
            self::prepare(new PEAR2_Pyrus_Package_Dependency($dep, $package, true));
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
            self::$transact->rollback();
            $reg = PEAR2_Pyrus_Config::current()->registry;
            $err = new PEAR2_MultiErrors;
            foreach (self::$installedPackages as $package) {
                try {
                    $reg->uninstall($package->name, $package->channel);
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
            self::$removedPackages = array();
            self::$inTransaction = false;
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
            foreach (self::$installPackages as $package) {
                // TODO: dependency validation
            }
            // download non-local packages
            foreach (self::$installPackages as $package) {
                $package->download();
            }
            // create dependency connections and load them into the directed graph
            foreach (self::$installPackages as $package) {
                $package->makeConnections($graph, self::$installPackages);
            }
            // topologically sort packages and install them via iterating over the graph
            foreach ($graph as $package) {
                $installer->install($package);
            }
            self::$transact->commit();
            $reg = PEAR2_Pyrus_Config::current()->registry;
            foreach (self::$installedPackages as $package) {
                $reg->install($package->getPackageFile()->info);
            }
            self::$installPackages = array();
        } catch (Exception $e) {
            self::rollback();
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
        
        self::$transact->begin();
        foreach ($package->installcontents as $file) {
            $channel = $package->channel;
            // {{{ assemble the destination paths
            if (!in_array($file->role,
                  PEAR2_Pyrus_Installer_Role::getValidRoles($package->getPackageType()))) {
                throw new PEAR2_Pyrus_Installer_Exception('Invalid role `' .
                        $file->role .
                        "' for file " . $file->name);
            }
            $role = PEAR2_Pyrus_Installer_Role::factory($package, $file->role,
                PEAR2_Pyrus_Config::current());
            $role->setup($this, $package, $file['attribs'], $file->name);
            if (!$role->isInstallable()) {
                continue;
            }
            $info = $role->processInstallation($package, $file['attribs'],
                $file->name, $tmp_path);
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
                if (!count($file->tasks)) { // no tasks
                    if (!file_exists($orig_file)) {
                        throw new PEAR2_Pyrus_Installer_Exception("file $orig_file does not exist");
                    }
                    if (!@copy($orig_file, $dest_file)) {
                        throw new PEAR2_Pyrus_Installer_Exception("failed to write $dest_file: $php_errormsg");
                    }
                    PEAR2_Pyrus_Log::log(3, "+ cp $orig_file $dest_file");
                    if (isset($attribs['md5sum'])) {
                        $md5sum = md5_file($dest_file);
                    }
                } else { // file with tasks
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
                    foreach ($file->tasks as $tag => $raw) {
                        $tag = str_replace(array($package->getTasksNs() . ':', '-'), 
                            array('', '_'), $tag);
                        $task = "PEAR2_Pyrus_Task_" . ucfirst($tag);
                        $task = new $task(PEAR2_Pyrus_Config::current(), PEAR2_PYRUS_TASK_INSTALL);
                        if (!$task->isScript()) { // scripts are only handled after installation
                            $task->init($raw, $file['attribs'], $lastversion);
                            $res = $task->startSession($package, $contents, $final_dest_file);
                            if ($res === false) {
                                continue; // skip this file
                            }
                            $contents = $res; // save changes
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
                    }
                }
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
                if (!OS_WINDOWS) {
                    if ($role->isExecutable()) {
                        $mode = 0777 & ~(int)octdec(PEAR2_Pyrus_Config::current()->umask);
                        PEAR2_Pyrus_Log::log(3, "+ chmod +x $dest_file");
                    } else {
                        $mode = 0666 & ~(int)octdec(PEAR2_Pyrus_Config::current()->umask);
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
}
