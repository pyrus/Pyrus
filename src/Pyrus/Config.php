<?php
/**
 * PEAR2_Pyrus_Config
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
 * Pyrus's master configuration manager
 *
 * Unlike PEAR version 1.x, the new Pyrus configuration manager is tightly bound
 * to include_path, and will search through include_path for system configuration
 * Pyrus installations.  In addition, the configuration is minimal.  If no changes
 * have been made since instantiation, no attempt is made to write out the configuration
 * file
 *
 * The User configuration file will be looked for in these locations:
 *
 * Unix:
 *
 * - home directory
 * - current directory
 *
 * Windows:
 *
 * - local settings directory on windows for the current user.
 *   This is looked up directly in the windows registry using COM
 * - current directory
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Config
{
    /**
     * location of primary PEAR2 installation
     *
     * @var string
     */
    protected $pearDir;

    /**
     * locations of all PEAR installations separated by PATH_SEPARATOR
     *
     * @var string
     */
    protected $pearPaths;

    /**
     * location of user-specific configuration file
     *
     * @var string
     */
    protected $userFile;

    /**
     * registry for this {@link $pearDir} value
     *
     * @var PEAR2_Pyrus_Registry
     */
    protected $myregistry;

    /**
     * channel registry for this {@link $pearDir} value
     *
     * @var PEAR2_Pyrus_Channel_Registry
     */
    protected $mychannelRegistry;
    /**
     * registry for plugins, which are kept in the plugin_dir directory
     *
     * @var PEAR2_Pyrus_PluginRegistry
     */
    protected $mypluginregistry;

    /**
     * configuration values for this configuration object
     *
     * @var string
     */
    protected $values;

    protected $hasPackagingRoot = false;

    static protected $explicitUserConfig = false;

    static protected $initializing = false;
    /**
     * mapping of path => PEAR2 configuration objects
     *
     * @var array
     */
    static protected $configs = array();

    /**
     * mapping of path => flag on modification
     *
     * If an index is not set, it has not been modified
     * @var array
     */
    static protected $configDirty = array();

    /**
     * The last instantiated configuration
     *
     * @var PEAR2_Pyrus_Config
     */
    static protected $current;

    /**
     * Default values for custom configuration values set by custom file roles.
     * @var array
     */
    static protected $customDefaults = array();

    /**
     * Default values for configuration.
     *
     * @php_dir@ is automatically replaced with the current
     * PEAR2 configuration location
     * @var array
     */
    static protected $defaults =
        array(
            'php_dir' => '@php_dir@/php', // pseudo-value in this implementation
            'ext_dir' => '@php_dir@/ext',
            'doc_dir' => '@php_dir@/docs',
            'bin_dir' => PHP_BINDIR,
            'data_dir' => '@php_dir@/data', // pseudo-value in this implementation
            'cfg_dir' => '@php_dir@/cfg',
            'www_dir' => '@php_dir@/www',
            'test_dir' => '@php_dir@/tests',
            'src_dir' => '@php_dir@/src',
            'php_bin' => '',
            'php_prefix' => '',
            'php_suffix' => '',
            'php_ini' => '',
            'default_channel' => 'pear2.php.net',
            'preferred_mirror' => 'pear2.php.net',
            'auto_discover' => 0,
            'http_proxy' => '',
            'cache_dir' => '@php_dir@/cache',
            'temp_dir' => '@php_dir@/temp',
            'download_dir' => '@php_dir@/downloads',
            'username' => '',
            'password' => '',
            'verbose' => 1,
            'preferred_state' => 'stable',
            'umask' => '0022',
            'cache_ttl' => 3600,
            'openssl_cert' => '',
            'handle' => '',
            'my_pear_path' => '@php_dir@',
            'plugins_dir' => '@default_config_dir@',
        );

    /**
     * Mapping of user configuration file path => config values
     *
     * @var array
     */
    static protected $userConfigs = array();

    static protected $lastUserConfig = null;

    /**
     * Configuration variable names that are bound to the PEAR installation
     *
     * These are values that should not change for different users
     * @var array
     */
    static protected $pearConfigNames = array(
            'php_dir', // pseudo-value in this implementation
            'ext_dir',
            'cfg_dir',
            'doc_dir',
            'bin_dir',
            'data_dir', // pseudo-value in this implementation
            'www_dir',
            'test_dir',
            'src_dir',
            'php_bin',
            'php_ini',
            'php_prefix',
            'php_suffix',
        );

    /**
     * Custom configuration variable names that are bound to the PEAR installation
     *
     * These are values that should not change for different users, and are
     * set by custom file roles
     * @var array
     */
    static protected $customPearConfigNames = array();

    /**
     * Configuration variable names that are user-specific
     *
     * These are values that are user preferences rather than
     * information necessary for installation on the filesystem.
     * @var array
     */
    static protected $userConfigNames = array(
            'default_channel',
            'auto_discover',
            'http_proxy',
            'cache_dir',
            'temp_dir',
            'verbose',
            'preferred_state',
            'umask',
            'cache_ttl',
            'my_pear_path', // PATH_SEPARATOR-separated list of PEAR repositories to manage
            'plugins_dir', // full path to location where pyrus plugins are installed
        );

    /**
     * Configuration variable names that are channel-specific
     * @var array
     */
    static protected $channelSpecificNames =
        array(
            'username',
            'password',
            'preferred_mirror',
            'download_dir',
            'openssl_cert',
            'handle',
        );

    /**
     * Configuration variable names that are user-specific
     *
     * These are values that are user preferences rather than
     * information necessary for installation on the filesystem, and
     * are set up by custom file roles
     * @var array
     */
    static protected $customUserConfigNames = array();

    /**
     * Configuration variable names that are channel-specific
     *
     * These are values that are channel-specific user preferences rather than
     * information necessary for installation on the filesystem, and
     * are set up by custom file roles
     * @var array
     */
    static protected $customChannelSpecificNames = array();

    /**
     * __get variables that cannot be used as custom config values
     * @var array
     */
    static protected $magicVars = array('registry',
                                        'channelregistry',
                                        'pluginregistry',
                                        'systemvars',
                                        'uservars',
                                        'mainsystemvars',
                                        'mainuservars',
                                        'userfile',
                                        'path');
    /**
     * Set up default configuration values that need to be determined at runtime
     *
     * The ext_dir variable, bin_dir variable, and php_ini are set up in
     * this method.
     */
    protected static function constructDefaults()
    {
        static $called = false;
        if ($called) {
            return;
        }

        $called = true;
        // set up default ext_dir
        if (getenv('PHP_PEAR_EXTENSION_DIR')) {
            self::$defaults['ext_dir'] = getenv('PHP_PEAR_EXTENSION_DIR');
            PEAR2_Pyrus_Log::log(5, 'used PHP_PEAR_EXTENSION_DIR environment variable');
        } elseif (ini_get('extension_dir')) {
            self::$defaults['ext_dir'] = ini_get('extension_dir');
            PEAR2_Pyrus_Log::log(5, 'used ini_get(extension_dir)');
        } elseif (defined('PEAR_EXTENSION_DIR')) {
            self::$defaults['ext_dir'] = PEAR_EXTENSION_DIR;
            PEAR2_Pyrus_Log::log(5, 'used PEAR_EXTENSION_DIR constant');
        }

        // set up default bin_dir
        if (getenv('PHP_PEAR_BIN_DIR')) {
            self::$defaults['bin_dir'] = getenv('PHP_PEAR_BIN_DIR');
            PEAR2_Pyrus_Log::log(5, 'used PHP_PEAR_BIN_DIR environment variable');
        } elseif (PATH_SEPARATOR == ';') {
            // we're on windows, and shouldn't use PHP_BINDIR
            do {
                if (!isset($_ENV) || !isset($_ENV['PATH'])) {
                    $path = getenv('PATH');
                } else {
                    $path = $_ENV['PATH'];
                }

                if (!$path) {
                    PEAR2_Pyrus_Log::log(5, 'used PHP_BINDIR on windows for bin_dir default');
                    break; // can't get PATH, so use PHP_BINDIR
                }

                $paths = explode(';', $path);
                foreach ($paths as $path) {
                    if ($path != '.' && is_writable($path)) {
                        // this place will do
                        PEAR2_Pyrus_Log::log(5, 'used ' . $path . ' for default bin_dir');
                        self::$defaults['bin_dir'] = $path;
                    }
                }
            } while (false);
        } else {
            PEAR2_Pyrus_Log::log(5, 'used PHP_BINDIR for bin_dir default');
        }

        // construct php_bin
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            if (file_exists(self::$defaults['bin_dir'] . DIRECTORY_SEPARATOR . 'php.exe')) {
                self::$defaults['php_bin'] = self::$defaults['bin_dir'] . DIRECTORY_SEPARATOR . 'php.exe';
            } else {
                foreach (explode(PATH_SEPARATOR, $_ENV['PATH']) as $path) {
                    if (file_exists($path . DIRECTORY_SEPARATOR . 'php.exe')) {
                        self::$defaults['php_bin'] = $path . DIRECTORY_SEPARATOR . 'php.exe';
                    }
                }
            }
        } else {
            if (file_exists(self::$defaults['bin_dir'] . DIRECTORY_SEPARATOR . 'php')) {
                self::$defaults['php_bin'] = self::$defaults['bin_dir'] . DIRECTORY_SEPARATOR . 'php';
            } elseif (isset($_ENV['PATH'])) {
                foreach (explode(PATH_SEPARATOR, $_ENV['PATH']) as $path) {
                    if (file_exists($path . DIRECTORY_SEPARATOR . 'php')) {
                        self::$defaults['php_bin'] = $path . DIRECTORY_SEPARATOR . 'php';
                    }
                }
            }
        }

        foreach (array_merge(self::$pearConfigNames,
                             self::$userConfigNames) as $name) {
            // make sure we've got valid paths for the underlying OS
            self::$defaults[$name] = str_replace('/', DIRECTORY_SEPARATOR,
                                                 self::$defaults[$name]);
        }

        self::$defaults['php_ini'] = php_ini_loaded_file();
        if (self::$defaults['php_ini']) {
            PEAR2_Pyrus_Log::log(5, 'Used ' . self::$defaults['php_ini'] . ' for php.ini location');
        } else {
            PEAR2_Pyrus_Log::log(5, 'Could not find php.ini');
        }
    }

    /**
     * parse a configuration for a PEAR2 installation
     *
     * @param string $pearDirectory This can be either a single path, or a
     *                              PATH_SEPARATOR-separated list of directories
     * @param string $userfile
     */
    protected function __construct($pearDirectory = false, $userfile = false)
    {
        self::$initializing = true;
        self::constructDefaults();
        if ($pearDirectory) {
            $pearDirectory = str_replace(array('\\', '//', '/'),
                                         array('/',  '/', DIRECTORY_SEPARATOR),
                                         $pearDirectory);
        }

        $this->loadUserSettings($pearDirectory, $userfile);
        $pearDirectory = $this->setupCascadingRegistries($pearDirectory);
        $this->loadConfigFile($pearDirectory);
        $this->pearDir = $this->pearPaths = $pearDirectory;
        if (strpos($pearDirectory, PATH_SEPARATOR)) {
            $this->pearDir = explode(PATH_SEPARATOR, $this->pearDir);
            $this->pearDir = $this->pearDir[0];
        }

        // Always set the current config to the most recently created one.
        $this->hasPackagingRoot = isset(PEAR2_Pyrus::$options['packagingroot']);
        self::$initializing = false;
    }

    static function initializing()
    {
        return self::$initializing;
    }

    function hasPackagingRoot()
    {
        return $this->hasPackagingRoot;
    }

    /**
     * Retrieve configuration for a PEAR2 installation
     *
     * @param string $pearDirectory
     * @param string $userfile
     * @return PEAR2_Pyrus_Config
     */
    static public function singleton($pearDirectory = false, $userfile = false)
    {
        if ($pearDirectory) {
            if (file_exists($pearDirectory)) {
                $pearDirectory = realpath($pearDirectory);
            }
            if (self::_OKPackagingRoot($pearDirectory)) {
                self::$current = self::$configs[$pearDirectory];
                return self::$configs[$pearDirectory];
            }
            $config = new static($pearDirectory, $userfile);
        } else {
            $config = new static(false, $userfile);
        }
        // now that we have a definitive path, check to see if
        // it exists
        if (self::_OKPackagingRoot($config->path)) {
            self::$current = self::$configs[$config->path];
            return self::$configs[$config->path];
        }
        $pearDirectory = $config->path;
        self::$configs[$pearDirectory] = $config;
        self::$current = $config;
        return $config;
    }

    private static function _OKPackagingRoot($path)
    {
        if (!isset(self::$configs[$path])) {
            return false;
        }
        if (isset(PEAR2_Pyrus::$options['packagingroot'])) {
            if (self::$configs[$path]->hasPackagingRoot()) {
                return true;
            }
        } else {
            if (!self::$configs[$path]->hasPackagingRoot()) {
                return true;
            }
        }
        return false;
    }

    /**
     * set the paths to scan for pyrus installations
     */
    public function setCascadingRegistries($path)
    {
        $paths = explode(PATH_SEPARATOR, $path);

        $paths = array_unique($paths);
        $readonly = false;
        foreach ($paths as $path) {
            try {
                if ($path === '.') continue;
                
                $registry_class        = PEAR2_Pyrus_Registry::$className;

                $registries = PEAR2_Pyrus_Registry::detectRegistries($path);
                if (!count($registries)) {
                    if ($readonly) {
                        // no installation present
                        continue;
                    }
                    $registries = array('Sqlite3', 'Xml');
                }

                $registry         = new $registry_class($path, $registries, $readonly);
                $channel_registry = $registry->getChannelRegistry();
                
                if (!$readonly) {
                    $this->myregistry        = $registry;
                    $this->mychannelRegistry = $channel_registry;
                }

                $readonly = true;
                
                $registry->setParent(); // clear any previous parent
                $channel_registry->setParent(); // clear any previous parent
                
                if (isset($last)) {
                    $last->setParent($registry);
                    $lastc->setParent($channel_registry);
                }

                $last  = $registry;
                $lastc = $channel_registry;
                if (isset(PEAR2_Pyrus::$options['packagingroot'])) {
                    break; // no cascading registries allowed for packaging
                }
            } catch (Exception $e) {
                if (!$readonly) {
                    throw new PEAR2_Pyrus_Config_Exception(
                        'Cannot initialize primary registry in path ' .
                        $path, $e);
                } else {
                    // silently skip this registry
                    continue;
                }
            }
        }
    }

    /**
     * @var string path to the configuration to set as the current config
     */
    static public function setCurrent($path)
    {
        if (isset(self::$configs[$path])) {
            self::$current = self::$configs[$path];
        } else {
            static::singleton($path);
        }
    }

    /**
     * Call to reset the registries after setting or resetting a packagingroot
     */
    function resetForPackagingRoot()
    {
        $this->setCascadingRegistries($this->pearPaths);
    }

    /**
     * Retrieve the currently active primary configuration
     * @return PEAR2_Pyrus_Config
     */
    static public function current()
    {
        if (isset(self::$current)) {
            return self::$current;
        }
        // default
        return static::singleton();
    }

    /**
     * Can be used to determine whether this user has ever run pyrus before
     */
    static public function userInitialized()
    {
        $userfile = static::getDefaultUserConfigFile();
        if (isset(self::$current)) {
            if (self::$current->userfile != $userfile) {
                // an explicit userfile was specified, so we assume this was intentional
                return true;
            }
        }
        if (!file_exists($userfile)) {
            // try cwd, this could work
            $test = realpath(getcwd() . DIRECTORY_SEPARATOR . 'pearconfig.xml');
            if ($test && file_exists($test)) {
                PEAR2_Pyrus_Log::log(5, 'User is initialized, found user configuration file in current directory' .
                    $userfile);
                return true;
            }
        } else {
            PEAR2_Pyrus_Log::log(5, 'User is initialized, found default user configuration file ' .
                $userfile);
            return true;
        }
        // no way to tell, must be explicit
        return false;
    }

    static public function getDefaultUserConfigFile()
    {
        if (class_exists('COM', false)) {
            return self::locateLocalSettingsDirectory() . DIRECTORY_SEPARATOR .
                'pear' . DIRECTORY_SEPARATOR . 'pearconfig.xml';
        } else {
            return self::locateLocalSettingsDirectory() . DIRECTORY_SEPARATOR .
                '.pear' . DIRECTORY_SEPARATOR . 'pearconfig.xml';
        }
    }

    /**
     * determines where user-specific configuration files should be saved.
     *
     * On unix, this is ~user/ or a location in /tmp based on the current directory.
     * On windows, this is your Documents and Settings folder.
     * @return string
     */
    static protected function locateLocalSettingsDirectory()
    {
        if (class_exists('COM', false)) {
            $shell = new COM('Wscript.Shell');
            $value = $shell->SpecialFolders('MyDocuments');
            return $value;
        }

        if (isset($_ENV['HOME'])) {
            return $_ENV['HOME'];
        } elseif ($e = getenv('HOME')) {
            return $e;
        }

        if (isset($_ENV['PWD'])) {
            $cwd = $_ENV['PWD'];
        } else {
            $cwd = getcwd();
        }
        
        return '/tmp/' . md5($cwd);
        
    }

    /**
     * Load the user configuration file
     *
     * This loads exclusively the user config
     * @return string path to the PEAR installation we are using
     */
    protected function loadUserSettings($pearDirectory, $userfile = false)
    {
        if (!$userfile) {
            if (self::$explicitUserConfig) {
                // never attempt to reload unless the user says so explicitly
                $this->userFile = self::$lastUserConfig;
                return;
            }
            $userfile = static::getDefaultUserConfigFile();

            if (!file_exists($userfile)) {
                $test = realpath(getcwd() . DIRECTORY_SEPARATOR . 'pearconfig.xml');
                if ($test && file_exists($test)) {
                    PEAR2_Pyrus_Log::log(5, 'Found user configuration file in current directory' .
                        $userfile);
                    $userfile = $test;
                }
            } else {
                PEAR2_Pyrus_Log::log(5, 'Found default user configuration file ' .
                    $userfile);
            }
        } else {
            self::$explicitUserConfig = true;
            PEAR2_Pyrus_Log::log(5, 'Using explicit user configuration file ' . $userfile);
        }

        $this->userFile = $userfile;
        self::$lastUserConfig = $userfile;
        if (!$userfile || !file_exists($userfile)) {
            PEAR2_Pyrus_Log::log(5, 'User configuration file ' . $userfile . ' not found');
            return;
        }

        if (isset(self::$userConfigs[$userfile])) {
            return;
        }

        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $x = simplexml_load_file($userfile);
        if (!$x) {
            $errors = libxml_get_errors();
            $e = new PEAR2_MultiErrors;
            foreach ($errors as $err) {
                $e->E_ERROR[] = new PEAR2_Pyrus_Config_Exception(trim($err->message));
            }
            libxml_clear_errors();
            throw new PEAR2_Pyrus_Config_Exception(
                'Unable to parse invalid user PEAR configuration at "' . $userfile . '"',
                $e);
        }

        $unsetvalues = array_diff(array_keys((array) $x),
                                  array_merge(self::$userConfigNames, self::$customUserConfigNames,
                                              self::$channelSpecificNames, self::$customChannelSpecificNames));
        // remove values that are not recognized user config variables
        foreach ($unsetvalues as $value) {
            if ($value == '@attributes') {
                continue;
            }
            PEAR2_Pyrus_Log::log(5, 'Removing unrecognized user configuration value ' .
                $value);
            unset($x->$value);
        }

        if (self::initializing()) {
            self::$userConfigs[$userfile] = (array) $x;
            foreach ($this->channelvars as $name) {
                if (!isset(self::$userConfigs[$userfile][$name])) {
                    continue;
                }
                self::$userConfigs[$userfile][$name] = (array) self::$userConfigs[$userfile][$name];
            }
            return;
        }
        $this->setupCascadingRegistries($pearDirectory);

        self::$userConfigs[$userfile] = (array) $x;
        foreach ($this->channelvars as $name) {
            self::$userConfigs[$userfile][$name] = (array) self::$userConfigs[$userfile][$name];
        }
    }

    /**
     * automatically cascade include_path here if necessary
     */
    function cascadePath($pearDirectory)
    {
        $paths = explode(PATH_SEPARATOR, $pearDirectory);
        $primary = $paths[0];
        if (count($paths) == 1) {
            // add registries within include_path by default
            // if explicit path is specified, user knows what they
            // are doing, don't add include_path
            $include_path = explode(PATH_SEPARATOR, get_include_path());
            foreach ($include_path as $i => $path) {
                if ($path === '.') {
                    continue;
                }
                if (substr($path, strlen($path) - 3) == 'php' && $path[strlen($path) - 4] == DIRECTORY_SEPARATOR) {
                    // include_path goes to the php_dir which is always php, so our config
                    // file is in the parent directory.
                    $extra[] = dirname($path);
                } else {
                    $extra[] = $path;
                }
            }
            PEAR2_Pyrus_Log::log(1, 'Automatically cascading include_path components ' . implode(', ', $extra));
            array_unshift($extra, $primary);
            $paths = $extra;
        }
        $paths = array_unique($paths);
        $pearDirectory = implode(PATH_SEPARATOR, $paths);
        return $pearDirectory;
    }

    function setupCascadingRegistries($pearDirectory)
    {
        if (!$this->my_pear_path) {
            if (!$pearDirectory) {
                $pearDirectory = getcwd();
                $this->my_pear_path = $pearDirectory = $this->cascadePath($pearDirectory);
            } else {
                $this->my_pear_path = $pearDirectory;
            }
            PEAR2_Pyrus_Log::log(5, 'Assuming my_pear_path is ' . $this->my_pear_path);
        } else {
            if (!$pearDirectory) {
                $pearDirectory = $this->my_pear_path;
            }
        }

        $this->setCascadingRegistries($pearDirectory);
        return $pearDirectory;
    }

    /**
     * Extract configuration from system + user configuration files
     *
     * Configuration is stored in XML format, in two locations.
     *
     * The system configuration contains all of the important directory
     * configuration variables like data_dir, and the location of php.ini and
     * the php executable php.exe or php.  This configuration is tightly bound
     * to the repository, and cannot be moved.  As such, php_dir is auto-defined
     * as dirname(/path/to/pear/.config), or /path/to/pear.
     *
     * Only 1 user configuration file is allowed, and contains user-specific
     * settings, including the locations where to download package releases
     * and where to cache files downloaded from the internet.  If false is passed
     * in, PEAR2_Pyrus_Config will attempt to guess at the config file location as
     * documented in the class docblock {@link PEAR2_Pyrus_Config}.
     * @param string $pearDirectory
     * @param string|false $userfile
     */
    protected function loadConfigFile($pearDirectory)
    {
        if (strpos($pearDirectory, PATH_SEPARATOR)) {
            $pearDirectory = explode(PATH_SEPARATOR, $pearDirectory);
            $pearDirectory = $pearDirectory[0];
        }
        if (isset(self::$configs[$pearDirectory]) ||
              !file_exists($pearDirectory . DIRECTORY_SEPARATOR . '.config')) {
            PEAR2_Pyrus_Log::log(5, 'Configuration not found for ' . $pearDirectory .
                ', assuming defaults');
            return;
        }
        PEAR2_Pyrus_Log::log(5, 'Loading configuration for ' . $pearDirectory);
        $this->helperLoadConfigFile($pearDirectory, $pearDirectory . DIRECTORY_SEPARATOR . '.config');
    }

    protected function helperLoadConfigFile($pearDirectory, $file, $extrainfo = '')
    {
        if ($extrainfo) {
            $extrainfo .= ' ';
        }
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $x = simplexml_load_file($file);
        if (!$x) {
            $errors = libxml_get_errors();
            $e = new PEAR2_MultiErrors;
            foreach ($errors as $err) {
                $e->E_ERROR[] = new PEAR2_Pyrus_Config_Exception(trim($err->message));
            }
            libxml_clear_errors();
            throw new PEAR2_Pyrus_Config_Exception(
                'Unable to parse invalid PEAR configuration ' . $extrainfo . 'at "' . $pearDirectory . '"',
                $e);
        }

        $unsetvalues = array_diff($keys = array_keys((array) $x),
                                  array_merge(self::$pearConfigNames, self::$customPearConfigNames));
        // remove values that are not recognized system config variables
        // both data_dir and php_dir are abstract values, delete them if present
        $unsetvalues[] = 'php_dir';
        $unsetvalues[] = 'data_dir';
        foreach ($unsetvalues as $value) {
            if (!in_array($value, $keys)) {
                continue;
            }

            PEAR2_Pyrus_Log::log(5, 'Removing unrecognized configuration ' . $extrainfo . 'value ' .
                $value);
            unset($x->$value);
        }
        $this->values = (array) $x;
    }

    /**
     * Save both the user configuration file and the system file
     *
     * If the userfile is not passed in, it is saved in the default
     * location which is either in ~/.pear/pearconfig.xml or on Windows
     * in the Documents and Settings directory
     * @param string $userfile path to alternate user configuration file
     */
    function saveConfig($userfile = false)
    {
        if (!$userfile) {
            if ($this->userFile) {
                $userfile = $this->userFile;
            } else {
                $userfile = self::getDefaultUserConfigFile();
            }
        }

        $userfile = str_replace(array('\\', '//', '/'),
                                array('/',  '/', DIRECTORY_SEPARATOR),
                                $userfile);

        $test = $userfile;
        while ($test && !file_exists($test)) {
            $test = dirname($test);
        }

        if (!is_writable($test)) {
            throw new PEAR2_Pyrus_Config_Exception('Cannot save configuration, no' .
                ' filesystem permissions to modify user configuration file ' . $userfile);
        }

        $test = $this->pearDir . '.config';
        if (isset(PEAR2_Pyrus::$options['packagingroot'])) {
            $test = PEAR2_Pyrus::prepend(PEAR2_Pyrus::$options['packagingroot'], $test);
        }
        while ($test && !file_exists($test)) {
            $test = dirname($test);
        }

        if (!is_writable($test)) {
            throw new PEAR2_Pyrus_Config_Exception('Cannot save configuration, no' .
                ' filesystem permissions to modify PEAR directory ' . $this->pearDir . '.config');
        }

        $x = simplexml_load_string('<pearconfig version="1.0"></pearconfig>');
        foreach (self::$userConfigNames as $var) {
            $x->$var = (string) $this->$var;
        }

        foreach (self::$customUserConfigNames as $var) {
            $x->$var = (string) $this->$var;
        }

        foreach (self::$channelSpecificNames as $key) {
            if (!isset(self::$userConfigs[$this->userfile][$key] )) {
                continue;
            }
            foreach (self::$userConfigs[$this->userfile][$key] as $safechan => $value) {
                $x->$key->$safechan = (string) $value;
            }
        }

        foreach (self::$customChannelSpecificNames as $key) {
            if (!isset(self::$userConfigs[$this->userfile][$key] )) {
                continue;
            }
            foreach (self::$userConfigs[$this->userfile][$key] as $safechan => $value) {
                $x->$key->$safechan = (string) $value;
            }
        }

        $userfiledir = dirname($userfile);
        if (!file_exists($userfiledir) && !@mkdir($userfiledir, 0777, true)) {
            throw new PEAR2_Pyrus_Config_Exception(
                'Unable to create directory ' . $userfiledir . ' to save ' .
                'user configuration ' . $userfile);
        }
        file_put_contents($userfile, $x->asXML());

        if (!isset(static::$configDirty[$this->pearDir])) {
            // no changes have been made to the config, no need to write it out
            return;
        }

        $system = $this->pearDir . '.config';
        if (dirname($system) != $this->pearDir) {
            $system = $this->pearDir . DIRECTORY_SEPARATOR . '.config';
        }
        if (isset(PEAR2_Pyrus::$options['packagingroot'])) {
            $system = PEAR2_Pyrus::prepend(PEAR2_Pyrus::$options['packagingroot'], $system);
        }
        if (!file_exists(dirname($system)) && !@mkdir(dirname($system), 0777, true)) {
            throw new PEAR2_Pyrus_Config_Exception(
                'Unable to create directory ' . dirname($system) . ' to save ' .
                'system configuration ' . $system);
        }

        $x = simplexml_load_string('<pearconfig version="1.0"></pearconfig>');
        $path = dirname($system) . DIRECTORY_SEPARATOR;
        foreach (self::$pearConfigNames as $var) {
            if ($var === 'php_dir' || $var === 'data_dir') {
                continue; // both of these are abstract
            }
            $x->$var = $this->$var;
        }

        foreach (self::$customPearConfigNames as $var) {
            $x->$var = $this->$var;
        }
        file_put_contents($system, $x->asXML());
        unset(static::$configDirty[$this->pearDir]);
        // save a snapshot for installation purposes
        static::configSnapshot();
    }

    /**
     * Save a snapshot of the current config, and return the file name
     *
     * If the latest snapshot is the same as the existing configuration,
     * simply return the filename
     * @return string basename of the snapshot file of the current configuration
     */
    static public function configSnapshot()
    {
        $conf = self::current();
        $snapshotdir = $conf->pearDir . DIRECTORY_SEPARATOR . '.configsnapshots';
        if (isset(PEAR2_Pyrus::$options['packagingroot'])) {
            $snapshotdir = PEAR2_Pyrus::prepend(PEAR2_Pyrus::$options['packagingroot'], $snapshotdir);
        }
        if (!file_exists($snapshotdir)) {
            // this will be simple - no snapshots exist yet
            if (!@mkdir($snapshotdir, 0755, true)) {
                throw new PEAR2_Pyrus_Config_Exception(
                    'Unable to create directory ' . $snapshotdir . ' to save ' .
                    'system configuration snapshots');
            }

            $snapshot = 'configsnapshot-' . date('Y-m-d H:i:s') . '.xml';
            $x = simplexml_load_string('<pearconfig version="1.0"></pearconfig>');
            foreach (self::$pearConfigNames as $var) {
                $x->$var = $conf->$var;
            }

            foreach (self::$customPearConfigNames as $var) {
                $x->$var = $conf->$var;
            }

            PEAR2_Pyrus_Log::log(5, 'Saving configuration snapshot ' . $snapshot);
            file_put_contents($snapshotdir . DIRECTORY_SEPARATOR . $snapshot, $x->asXML());
            return $snapshot;
        }
        // scan existing snapshots, if any, for a match
        $dir = opendir($snapshotdir);
        while (false !== ($snapshot = readdir($dir))) {
            if ($snapshot[0] == '.') continue;
            $x = simplexml_load_file($snapshotdir . DIRECTORY_SEPARATOR . $snapshot);
            foreach (self::$pearConfigNames as $var) {
                if ($x->$var != $conf->$var) continue 2;
            }

            foreach (self::$customPearConfigNames as $var) {
                if (!isset($x->var) || $x->$var != $conf->$var) continue 2;
            }

            // found a match
            PEAR2_Pyrus_Log::log(5, 'Found matching configuration snapshot ' . $snapshot);
            return $snapshot;
        }
        PEAR2_Pyrus_Log::log(5, 'No matching configuration snapshot found');
        // no matches found
        $snapshot = 'configsnapshot-' . date('Y-m-d H:i:s') . '.xml';
        $i = 0;
        while (file_exists($snapshotdir . DIRECTORY_SEPARATOR . $snapshot)) {
            $i++;
            // keep appending ".1" until we get a unique filename
            $snapshot = 'configsnapshot-' . date('Y-m-d H:i:s') . str_repeat('.1', $i) . '.xml';
        }
        // save the snapshot
        $x = simplexml_load_string('<pearconfig version="1.0"></pearconfig>');
        foreach (self::$pearConfigNames as $var) {
            $x->$var = $conf->$var;
        }

        foreach (self::$customPearConfigNames as $var) {
            $x->$var = $conf->$var;
        }

        PEAR2_Pyrus_Log::log(5, 'Saving configuration snapshot ' . $snapshot);
        file_put_contents($snapshotdir . DIRECTORY_SEPARATOR . $snapshot, $x->asXML());
        return $snapshot;
    }

    /**
     * Add a new custom configuration variable
     * @param string $key variable name
     * @param string $default default value
     * @param string $system one of system, user or channel-specific
     */
    static public function addConfigValue($key, $default, $system = 'system')
    {
        if (in_array($key, self::$magicVars, true)) {
            throw new PEAR2_Pyrus_Config_Exception('Invalid custom configuration variable '
                                                   . $key . ', already in use for retrieving configuration information');
        }

        if (!preg_match('/^[a-z0-9-_]+\\z/', $key)) {
            throw new PEAR2_Pyrus_Config_Exception('Invalid custom configuration variable name "'.  $key . '"');
        }

        if ($system === 'system') {
            if (in_array($key, self::$pearConfigNames)) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing configuration value "' . $key . '"');
            }

            if (in_array($key, self::$customPearConfigNames)) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom configuration value "' . $key .
                                                       '"');
            }

            if (in_array($key, array_merge(self::$userConfigNames, self::$channelSpecificNames))) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing user configuration value "' . $key .
                                                       '" with system value');
            }

            if (in_array($key, array_merge(self::$customUserConfigNames, self::$customChannelSpecificNames))) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom user configuration value "' .
                                                       $key . '" with system value');
            }
            $var = 'customPearConfigNames';
        } else {
            if (in_array($key, self::$pearConfigNames)) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing configuration value "' .
                                                       $key . '" with user value');
            }

            if (in_array($key, self::$customPearConfigNames)) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom configuration value "' .
                                                       $key . '" with user value');
            }

            if (in_array($key, array_merge(self::$userConfigNames, self::$channelSpecificNames))) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing user configuration value "' .
                                                       $key . '"');
            }

            if (in_array($key, array_merge(self::$customUserConfigNames, self::$customChannelSpecificNames))) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom user configuration value "'
                                                       . $key . '"');
            }
            $var = 'customUserConfigNames';
            if ($system === 'channel-specific') {
                self::$customChannelSpecificNames[] = $key;
                self::$customDefaults[$key] = $default;
                return;
            }
        }
        self::${$var}[count(self::${$var})] = $key;
        self::$customDefaults[$key] = $default;
    }

    public function defaultValue($key)
    {
        if (isset(self::$defaults[$key])) {
            if ($key === 'verbose') {
                // this prevents a rather nasty loop if logging is checking on verbose
                return self::$defaults['verbose'];
            }
            PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' .
                                 $key .
                ' default value "' . self::$defaults[$key] . '"');
            $ret = str_replace('@php_dir@', $this->pearDir, self::$defaults[$key]);
            PEAR2_Pyrus_Log::log(5, 'Replacing @default_config_dir@ for config variable ' .
                                 $key .
                ' default value "' . self::$defaults[$key] . '"');
            return str_replace('@default_config_dir@', dirname($this->userFile), $ret);
        }
        PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' .
                             $key .
            ' default value "' . self::$customDefaults[$key] . '"');
        $ret = str_replace('@php_dir@', $this->pearDir, self::$customDefaults[$key]);
        PEAR2_Pyrus_Log::log(5, 'Replacing @default_config_dir@ for config variable ' .
                             $key .
            ' default value "' . self::$customDefaults[$key] . '"');
        return str_replace('@default_config_dir@', dirname($this->userFile), $ret);
    }

    public function __get($key)
    {
        if (in_array($key, array_merge(self::$pearConfigNames, self::$userConfigNames,
                                          self::$customPearConfigNames,
                                          self::$customUserConfigNames,
                                          self::$channelSpecificNames,
                                          self::$customChannelSpecificNames))) {
            if ((!isset(self::$userConfigs[$this->userFile][$key])
                && !isset($this->values[$key])) || $key === 'php_dir'
                || $key === 'data_dir'
            ) {
return_default_value:
                if (isset(self::$defaults[$key])) {
                    if ($key === 'verbose') {
                        // this prevents a rather nasty loop if logging is checking on verbose
                        return self::$defaults['verbose'];
                    }
                    if ($key === 'preferred_mirror') {
                        return $this->default_channel;
                    }
                    PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' .
                                         $key .
                        ' default value "' . self::$defaults[$key] . '"');
                    $ret = str_replace('@php_dir@', $this->pearDir, self::$defaults[$key]);
                    PEAR2_Pyrus_Log::log(5, 'Replacing @default_config_dir@ for config variable ' .
                                         $key .
                        ' default value "' . self::$defaults[$key] . '"');
                    return str_replace('@default_config_dir@', dirname($this->userFile), $ret);
                } else {
                    PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' .
                                         $key .
                        ' default value "' . self::$customDefaults[$key] . '"');
                    $ret = str_replace('@php_dir@', $this->pearDir, self::$customDefaults[$key]);
                    PEAR2_Pyrus_Log::log(5, 'Replacing @default_config_dir@ for config variable ' .
                                         $key .
                        ' default value "' . self::$customDefaults[$key] . '"');
                    return str_replace('@default_config_dir@', dirname($this->userFile), $ret);
                }
            }

            if (in_array($key, array_merge(self::$pearConfigNames,
                                             self::$customPearConfigNames))) {
                PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' . $key .
                    ' value "' . $this->values[$key] . '"');
                $ret = str_replace('@php_dir@', $this->pearDir, $this->values[$key]);
                PEAR2_Pyrus_Log::log(5, 'Replacing @default_config_dir@ for config variable ' .
                                     $key .
                    ' default value "' . $this->values[$key] . '"');
                return str_replace('@default_config_dir@', dirname($this->userFile), $ret);
            }

            if (in_array($key, array_merge(self::$channelSpecificNames,
                                             self::$customChannelSpecificNames))) {
                $chan = $this->safeName($this->default_channel);
                if (isset(self::$userConfigs[$this->userfile][$key][$this->safeName($chan)])) {
                    return self::$userConfigs[$this->userfile][$key][$this->safeName($chan)];
                }
                goto return_default_value;
            }
            return self::$userConfigs[$this->userFile][$key];
        }

        if ($key == 'path') {
            return $this->pearPaths;
        }

        if ($key == 'location') {
            return $this->pearDir;
        }

        if ($key == 'registry') {
            return $this->myregistry;
        }

        if ($key == 'pluginregistry') {
            if (!isset($this->mypluginregistry)) {
                $this->mypluginregistry = new PEAR2_Pyrus_PluginRegistry($this->__get('plugins_dir'));
            }
            return $this->mypluginregistry;
        }

        if ($key == 'channelregistry') {
            return $this->mychannelRegistry;
        }

        if ($key == 'systemvars') {
            return array_merge(self::$pearConfigNames, self::$customPearConfigNames);
        }

        if ($key == 'uservars') {
            return array_merge(self::$userConfigNames, self::$customUserConfigNames,
                               self::$channelSpecificNames, self::$customChannelSpecificNames);
        }

        if ($key == 'channelvars') {
            return array_merge(self::$channelSpecificNames, self::$customChannelSpecificNames);
        }

        if ($key == 'mainsystemvars') {
            return self::$pearConfigNames;
        }

        if ($key == 'mainuservars') {
            return self::$userConfigNames;
        }

        if ($key == 'mainchannelvars') {
            return self::$channelSpecificNames;
        }

        if ($key == 'userfile') {
            return $this->userFile;
        }

        if ($key == 'customsystemvars') {
            return self::$customPearConfigNames;
        }

        if ($key == 'customuservars') {
            return self::$customUserConfigNames;
        }
        if ($key == 'customchannelvars') {
            return self::$customChannelSpecificNames;
        }

        throw new PEAR2_Pyrus_Config_Exception(
            'Unknown configuration variable "' . $key . '" in location ' .
            $this->pearDir);
    }

    /**
     * Make a channel name safe for an xml element name
     */
    protected function safeName($name)
    {
        return str_replace(array('/','.'), array('SLASH','DOT'), $name);
    }

    public function __unset($key)
    {
        if (in_array($key, self::$magicVars, true)) {
            throw new PEAR2_Pyrus_Config_Exception('Cannot unset magic value ' . $key);
        }

        if ($key === 'php_dir' || $key === 'data_dir') {
            throw new PEAR2_Pyrus_Config_Exception('Cannot unset ' . $key);
        }

        if (isset($this->values[$key])) {
            unset($this->values[$key]);
            static::$configDirty[$this->pearDir] = 1;
            return;
        }

        if (isset(self::$userConfigs[$this->userFile][$key])) {
            if (in_array($key, array_merge(self::$channelSpecificNames, self::$customChannelSpecificNames))) {
                if (isset(self::$userConfigs[$this->userFile][$key][$this->safeName($this->default_channel)])) {
                    unset(self::$userConfigs[$this->userFile][$key][$this->safeName($this->default_channel)]);
                    if (!count(self::$userConfigs[$this->userFile][$key][$this->safeName($this->default_channel)])) {
                        unset(self::$userConfigs[$this->userFile][$key]);
                    }
                }
            } else {
                unset(self::$userConfigs[$this->userFile][$key]);
            }
        }
    }

    public function __isset($key)
    {
        if (in_array($key, self::$magicVars, true)) {
            return true;
        }

        if ($key === 'php_dir' || $key === 'data_dir') {
            return true;
        }

        if (in_array($key, self::$pearConfigNames)
            || in_array($key, self::$customPearConfigNames)) {
            return isset($this->values[$key]);
        }

        if (in_array($key, array_merge(self::$channelSpecificNames, self::$customChannelSpecificNames))) {
            return isset(self::$userConfigs[$this->userFile][$key][$this->safeName($this->default_channel)]);
        }
        return isset(self::$userConfigs[$this->userFile][$key]);
    }

    public function __set($key, $value)
    {
        if (in_array($key, self::$magicVars, true)) {
            throw new PEAR2_Pyrus_Config_Exception('Cannot set magic configuration variable ' . $key);
        }

        if ($key == 'php_dir') {
            throw new PEAR2_Pyrus_Config_Exception('Cannot set php_dir, php_dir is always php/ in the repository');
        }

        if ($key == 'data_dir') {
            throw new PEAR2_Pyrus_Config_Exception('Cannot set data_dir, data_dir is always data/ in the repository');
        }

        if (!isset(self::$defaults[$key]) && !isset(self::$customDefaults[$key])) {
            throw new PEAR2_Pyrus_Config_Exception(
                'Unknown configuration variable "' . $key . '" in location ' .
                $this->pearDir);
        }

        if (in_array($key, self::$pearConfigNames)
            || in_array($key, self::$customPearConfigNames)) {
            $this->values[$key] = $value;
            static::$configDirty[$this->pearDir] = 1;
            return;
        }

        if (in_array($key, array_merge(self::$channelSpecificNames, self::$customChannelSpecificNames))) {
            if (!isset(self::$userConfigs[$this->userFile][$key][$this->safeName($this->default_channel)])) {
                self::$userConfigs[$this->userFile][$key][$this->safeName($this->default_channel)] = array();
            }
            self::$userConfigs[$this->userFile][$key][$this->safeName($this->default_channel)] = $value;
        } else {
            self::$userConfigs[$this->userFile][$key] = $value;
        }
    }

    /**
     * Parse a string to determine which package file is requested
     *
     * This differentiates between the three kinds of packages:
     *
     *  - local files
     *  - remote static URLs
     *  - dynamic abstract package names
     * @param string $pname
     * @return string|array A string is returned if this is a file, otherwise an array
     *                      containing information is returned
     */
    static public function parsePackageName($pname, $assumeabstract = false)
    {
        if (!$assumeabstract && @file_exists($pname) && @is_file($pname)) {
            return $pname;
        }
        return self::current()->channelregistry->parseName($pname);
    }

    static public function parsedPackageNameToString($name)
    {
        return self::current()->channelregistry->parsedNameToString($name);
    }
}
