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
 * Pyrus installations.
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
     * location of PEAR2 installation
     *
     * @var string
     */
    protected $pearDir;

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
     * configuration values for this configuration object
     *
     * @var string
     */
    protected $values;

    /**
     * mapping of path => PEAR2 configuration objects
     *
     * @var array
     */
    static protected $configs = array();

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
            'php_dir' => '@php_dir@/src', // pseudo-value in this implementation
            'ext_dir' => '@php_dir@/ext_dir',
            'doc_dir' => '@php_dir@/docs',
            'bin_dir' => PHP_BINDIR,
            'data_dir' => '@php_dir@/data', // pseudo-value in this implementation
            'cfg_dir' => '@php_dir@/cfg',
            'www_dir' => '@php_dir@/www',
            'test_dir' => '@php_dir@/tests',
            'php_bin' => '',
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
            'sig_type' => '',
            'sig_bin' => '',
            'sig_keyid' => '',
            'sig_keydir' => '',
            'my_pear_path' => '@php_dir@',
        );

    /**
     * Mapping of user configuration file path => config values
     *
     * @var array
     */
    static protected $userConfigs = array();

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
            'php_bin',
            'php_ini',
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
            'preferred_mirror',
            'auto_discover',
            'http_proxy',
            'cache_dir',
            'temp_dir',
            'download_dir',
            'username',
            'password',
            'verbose',
            'preferred_state',
            'umask',
            'cache_ttl',
            'sig_type',
            'sig_bin',
            'sig_keyid',
            'sig_keydir',
            'my_pear_path', // PATH_SEPARATOR-separated list of PEAR repositories to manage
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
     * __get variables that cannot be used as custom config values
     * @var array
     */
    static protected $magicVars = array('registry',
                                        'channelregistry',
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
        self::constructDefaults();
        if ($pearDirectory) {
            $pearDirectory = str_replace(array('\\', '//', '/'),
                                         array('/',  '/', DIRECTORY_SEPARATOR),
                                         $pearDirectory);
        }

        $this->loadUserSettings($pearDirectory, $userfile);
        if ($pearDirectory) {
            $this->loadConfigFile($pearDirectory);
            $this->setCascadingRegistries($pearDirectory);
        }
        self::$configs[$pearDirectory] = $this;
        $this->pearDir = $pearDirectory;

        // Always set the current config to the most recently created one.
        self::$current = $this;
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
        if (isset(self::$configs[$pearDirectory])) {
            return self::$configs[$pearDirectory];
        }

        self::$configs[$pearDirectory] = new PEAR2_Pyrus_Config($pearDirectory, $userfile);
        return self::$configs[$pearDirectory];
    }

    /**
     * set the path to scan for pyrus installations
     */
    public function setCascadingRegistries($path)
    {
        $paths = explode(PATH_SEPARATOR, $path);
        $ret = $paths[0];
        if (count($paths) == 1) {
            // add registries within include_path by default
            // if explicit path is specified, user knows what they
            // are doing, don't add include_path
            PEAR2_Pyrus_Log::log(1, 'Automatically cascading include_path');
            $extra = explode(PATH_SEPARATOR, get_include_path());
            foreach ($extra as $i => $path) {
                if (substr($path, strlen($path) - 3) == 'src' && $path[strlen($path) - 4] == DIRECTORY_SEPARATOR) {
                    // include_path goes to the php_dir which is always src, so our config
                    // file is in the parent directory.
                    $extra[$i] = dirname($path);
                }
            }
            array_unshift($extra, $ret);
            $paths = $extra;
        }

        $paths = array_unique($paths);
        $start = true;
        foreach ($paths as $path) {
            try {
                if ($path === '.') continue;
                $a = PEAR2_Pyrus_Registry::$className;
                $reg = new $a($path, array('Sqlite3', 'Xml'), !$start);

                if ($start) {
                    $this->myregistry = $reg;
                }

                $reg->setParent(); // clear any previous parent
                $b = PEAR2_Pyrus_ChannelRegistry::$className;
                $regc = new $b($path, array('Sqlite3', 'Xml'), !$start);
                if ($start) {
                    $this->mychannelRegistry = $regc;
                }

                $start = false;
                $regc->setParent(); // clear any previous parent
                if (isset($last)) {
                    $last->setParent($reg);
                    $lastc->setParent($regc);
                }

                $last = $reg;
                $lastc = $regc;
            } catch (Exception $e) {
                if ($start) {
                    throw new PEAR2_Pyrus_Config_Exception(
                        'Cannot initialize primary registry in path ' .
                        $path, $e);
                } else {
                    // silently skip this registry
                    continue;
                }
            }
        }
        return $ret;
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
        return PEAR2_Pyrus_Config::singleton();
    }

    /**
     * Can be used to determine whether this user has ever run pyrus before
     */
    static public function userInitialized()
    {
        $userfile = static::getDefaultUserConfigFile();
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

        return '/tmp/' . md5($_ENV['PWD']);
    }

    /**
     * Load the user configuration file
     *
     * This loads exclusively the user config
     */
    protected function loadUserSettings($pearDirectory, $userfile = false)
    {
        if (!$userfile) {
            $userfile = self::getDefaultUserConfigFile();

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
            PEAR2_Pyrus_Log::log(5, 'Using explicit user configuration file ' . $userfile);
        }

        $this->userFile = $userfile;
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

        $unsetvalues = array_diff(array_keys((array) $x), array_merge(self::$userConfigNames, self::$customUserConfigNames));
        // remove values that are not recognized user config variables
        foreach ($unsetvalues as $value) {
            if ($value == '@attributes') {
                continue;
            }
            PEAR2_Pyrus_Log::log(5, 'Removing unrecognized user configuration value ' .
                $value);
            unset($x->$value);
        }

        if (!$x->my_pear_path) {
            if (!$pearDirectory) {
                $pearDirectory = getcwd();
            }
            $pearDirectory = $this->setCascadingRegistries((string)$pearDirectory);
            $x->my_pear_path = $pearDirectory;
            PEAR2_Pyrus_Log::log(5, 'Assuming my_pear_path is ' . $pearDirectory);
        } else {
            if (!$pearDirectory) {
                $pearDirectory = $this->setCascadingRegistries((string) $x->my_pear_path);
            } else {
                // ensure that $pearDirectory is a part of this cascading directory path
                $pearDirectory = $this->setCascadingRegistries((string)$pearDirectory .
                        PATH_SEPARATOR . $x->my_pear_path);
            }
        }

        self::$userConfigs[$userfile] = (array) $x;
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
        if (isset(self::$configs[$pearDirectory]) ||
              !file_exists($pearDirectory . DIRECTORY_SEPARATOR . '.config')) {
            PEAR2_Pyrus_Log::log(5, 'Configuration not found for ' . $pearDirectory .
                ', assuming defaults');
            return;
        }

        PEAR2_Pyrus_Log::log(5, 'Loading configuration for ' . $pearDirectory);
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $x = simplexml_load_file($pearDirectory . DIRECTORY_SEPARATOR . '.config');
        if (!$x) {
            $errors = libxml_get_errors();
            $e = new PEAR2_MultiErrors;
            foreach ($errors as $err) {
                $e->E_ERROR[] = new PEAR2_Pyrus_Config_Exception(trim($err->message));
            }
            libxml_clear_errors();
            throw new PEAR2_Pyrus_Config_Exception(
                'Unable to parse invalid PEAR configuration at "' . $pearDirectory . '"',
                $e);
        }

        $unsetvalues = array_diff(array_keys((array) $x), array_merge(self::$pearConfigNames, self::$customPearConfigNames));
        // remove values that are not recognized system config variables
        foreach ($unsetvalues as $value) {
            if ($value == '@attributes') {
                continue;
            }

            if ($value === 'php_dir' || $value === 'data_dir') {
                unset($x->$value); // both of these are abstract
            }

            PEAR2_Pyrus_Log::log(5, 'Removing unrecognized configuration value ' .
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
                // FIXME any reason why we don't name it .pear on windows ? no special meaning
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

        $userfiledir = dirname($userfile);
        if (!file_exists($userfiledir) && !@mkdir($userfiledir, 0777, true)) {
            throw new PEAR2_Pyrus_Config_Exception(
                'Unable to create directory ' . $userfiledir . ' to save ' .
                'user configuration ' . $userfile);
        }
        file_put_contents($userfile, $x->asXML());

        $system = $this->pearDir . '.config';
        if (dirname($system) != $this->pearDir) {
            $system = $this->pearDir . DIRECTORY_SEPARATOR . '.config';
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
            file_put_contents($path . $var . '.txt', $this->$var);
        }

        foreach (self::$customPearConfigNames as $var) {
            $x->$var = $this->$var;
            file_put_contents($path . $var . '.txt', $this->$var);
        }
        file_put_contents($system, $x->asXML());
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
     * Load a configuration
     */
    static public function addConfigValue($key, $default, $system = true)
    {
        if (in_array($key, self::$magicVars, true)) {
            throw new PEAR2_Pyrus_Config_Exception('Invalid custom configuration variable, already in use for retrieving configuration information');
        }

        if (!preg_match('/^[a-z0-9-_]+\\z/', $key)) {
            throw new PEAR2_Pyrus_Config_Exception('Invalid custom configuration variable name "'.  $key . '"');
        }

        if ($system) {
            if (isset(self::$pearConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing configuration value "' . $key . '"');
            }

            if (isset(self::$customPearConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom configuration value "' . $key . '"');
            }

            if (isset(self::$userConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing user configuration value "' . $key . '" with system value');
            }

            if (isset(self::$customUserConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom user configuration value "' . $key . '" with system value');
            }
            $var = 'customPearConfigNames';
        } else {
            if (isset(self::$userConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing configuration value "' . $key . '"');
            }

            if (isset(self::$customUserConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom configuration value "' . $key . '"');
            }

            if (isset(self::$pearConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing configuration value "' . $key . '" with user value');
            }

            if (isset(self::$customPearConfigNames[$key])) {
                throw new PEAR2_Pyrus_Config_Exception('Cannot override existing custom configuration value "' . $key . '" with user value');
            }
            $var = 'customUserConfigNames';
        }
        self::${$var}[count(self::${$var})] = $key;
        self::$customDefaults[$key] = $default;
    }

    public function __get($key)
    {
        if (in_array($key, array_merge(self::$pearConfigNames, self::$userConfigNames,
                                          self::$customPearConfigNames,
                                          self::$customUserConfigNames))) {
            if ((!isset(self::$userConfigs[$this->userFile][$key])
                && !isset($this->values[$key])) || $key === 'php_dir'
                || $key === 'data_dir'
            ) {
                if (isset(self::$defaults[$key])) {
                    PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' .
                                         $key .
                        ' default value "' . self::$defaults[$key] . '"');
                    return str_replace('@php_dir@', $this->pearDir, self::$defaults[$key]);
                } else {
                    PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' .
                                         $key .
                        ' default value "' . self::$customDefaults[$key] . '"');
                    return str_replace('@php_dir@', $this->pearDir,
                                       self::$customDefaults[$key]);
                }
            }

            if (in_array($key, array_merge(self::$pearConfigNames,
                                             self::$customPearConfigNames))) {
                PEAR2_Pyrus_Log::log(5, 'Replacing @php_dir@ for config variable ' . $key .
                    ' value "' . $this->values[$key] . '"');
                return str_replace('@php_dir@', $this->pearDir,
                    $this->values[$key]);
            }

            return self::$userConfigs[$this->userFile][$key];
        }

        if ($key == 'registry') {
            return $this->myregistry;
        }

        if ($key == 'channelregistry') {
            return $this->mychannelRegistry;
        }

        if ($key == 'systemvars') {
            return array_merge(self::$pearConfigNames, self::$customPearConfigNames);
        }

        if ($key == 'uservars') {
            return array_merge(self::$userConfigNames, self::$customUserConfigNames);
        }

        if ($key == 'mainsystemvars') {
            return self::$pearConfigNames;
        }

        if ($key == 'mainuservars') {
            return self::$userConfigNames;
        }

        if ($key == 'userfile') {
            return $this->userFile;
        }

        if ($key == 'path') {
            return $this->pearDir;
        }

        if ($key == 'customsystemvars') {
            return self::$customPearConfigNames;
        }

        if ($key == 'customuservars') {
            return self::$customUserConfigNames;
        }

        throw new PEAR2_Pyrus_Config_Exception(
            'Unknown configuration variable "' . $key . '" in location ' .
            $this->pearDir);
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
            return;
        }

        if (isset(self::$userConfigs[$this->userFile][$key])) {
            unset(self::$userConfigs[$this->userFile][$key]);
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

        return isset(self::$userConfigs[$this->userFile][$key]);
    }

    public function __set($key, $value)
    {
        if (in_array($key, self::$magicVars, true)) {
            throw new PEAR2_Pyrus_Config_Exception('Cannot set magic configuration variable ' . $key);
        }

        if ($key == 'php_dir' || $key == 'data_dir') {
            throw new PEAR2_Pyrus_Config_Exception('Cannot set php_dir, move the repository');
        }

        if (!isset(self::$defaults[$key]) && !isset(self::$customDefaults[$key])) {
            throw new PEAR2_Pyrus_Config_Exception(
                'Unknown configuration variable "' . $key . '" in location ' .
                $this->pearDir);
        }

        if (in_array($key, self::$pearConfigNames)
            || in_array($key, self::$customPearConfigNames)) {
            $this->values[$key] = $value;
        }

        self::$userConfigs[$this->userFile][$key] = $value;
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
