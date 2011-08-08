<?php
/**
 * \Pyrus\Installer\Role
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

/**
 * Base class for installation roles for files.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Installer;
use \Pyrus\Config as Config;
class Role
{
    static private $_roles;

    /**
     * Set up any additional configuration variables that file roles require
     *
     * Never call this directly, it is called by \Pyrus\Config constructor
     * @param \Pyrus\Config
     * @access private
     * @static
     */
    public static function initializeConfig(Config $config)
    {
        if (!isset(self::$_roles)) {
            self::registerRoles();
        }

        foreach (self::$_roles as $class => $info) {
            if (!$info['config_vars']) {
                continue;
            }

            $config->addConfigValue($info['config_vars']);
        }
    }

    /**
     * @param string package type
     * @param string role name
     * @static
     */
    static function factory($packagetype, $role)
    {
        if (!isset(self::$_roles)) {
            self::registerRoles();
        }

        if (!in_array($role, self::getValidRoles($packagetype))) {
            throw new Exception('Invalid role ' . $role . ' requested for package type ' . $packagetype);
        }

        $class = self::$_roles[$role]['class'];
        if (!class_exists($class, true)) {
            throw new Exception('Unable to load custom class ' . $class . ' for ' . $role);
        }
        return new $class(Config::current(), self::$_roles[$role]);
    }

    /**
     * Get a list of file roles that are valid for the particular release type.
     *
     * For instance, src files serve no purpose in regular php releases.
     * @param string
     * @param bool clear cache
     * @return array
     * @static
     */
    static function getValidRoles($release, $clear = false)
    {
        if (!isset(self::$_roles)) {
            self::registerRoles();
        }

        static $ret = array();
        if ($clear) {
            $ret = array();
        }

        if (isset($ret[$release])) {
            return $ret[$release];
        }

        $ret[$release] = array();
        foreach (self::$_roles as $role => $okreleases) {
            if (in_array($release, $okreleases['releasetypes'])) {
                $ret[$release][] = $role;
            }
        }

        return $ret[$release];
    }

    /**
     * Get a list of roles that require their files to be installed
     *
     * Most roles must be installed, but src and package roles, for instance
     * are pseudo-roles.  src files are compiled into a new extension.  Package
     * roles are actually fully bundled releases of a package
     * @param bool clear cache
     * @return array
     * @static
     */
    static function getInstallableRoles($clear = false)
    {
        if (!isset(self::$_roles)) {
            self::registerRoles();
        }

        static $ret;
        if ($clear) {
            unset($ret);
        }

        if (!isset($ret)) {
            $ret = array();
            foreach (self::$_roles as $role => $okreleases) {
                if ($okreleases['installable']) {
                    $ret[] = $role;
                }
            }
        }

        return $ret;
    }

    /**
     * Return an array of roles that are affected by the baseinstalldir attribute
     *
     * Most roles ignore this attribute, and instead install directly into:
     * PackageName/filepath
     * so a tests file tests/file.phpt is installed into PackageName/tests/filepath.php
     * @param bool clear cache
     * @return array
     * @static
     */
    static function getBaseinstallRoles($clear = false)
    {
        if (!isset(self::$_roles)) {
            self::registerRoles();
        }

        static $ret;
        if ($clear) {
            unset($ret);
        }

        if (!isset($ret)) {
            $ret = array();
            foreach (self::$_roles as $role => $okreleases) {
                if ($okreleases['honorsbaseinstall']) {
                    $ret[] = $role;
                }
            }
        }

        return $ret;
    }

    /**
     * Scan through the Command directory looking for classes
     * and see what commands they implement.
     * @param string which directory to look for classes, defaults to
     *               the Installer/Roles subdirectory of
     *               the directory from where this file (__FILE__) is
     *               included.
     *
     * @return bool TRUE on success, a PEAR error on failure
     * @access public
     * @static
     */
    static function registerRoles($dir = null)
    {
        self::$_roles = array();
        $parser = new \Pyrus\XMLParser;
        if ($dir === null) {
            $dir = __DIR__ . '/Role';
        }

        if (!file_exists($dir) || !is_dir($dir)) {
            throw new Role\Exception("registerRoles: opendir($dir) failed");
        }

        $dp = @opendir($dir);
        if (empty($dp)) {
            throw new Role\Exception("registerRoles: opendir($dir) failed");
        }

        $schemapath = \Pyrus\Main::getDataPath() . '/customrole-2.0.xsd';
        while ($entry = readdir($dp)) {
            if ($entry{0} == '.' || substr($entry, -4) != '.xml') {
                continue;
            }

            $role = strtolower(basename($entry, '.xml'));
            // List of roles
            if (!isset(self::$_roles[$role])) {
                $file = "$dir/$entry";
                $data = $parser->parse($file, $schemapath);
                $data = $data['role'];
                if (!is_array($data['releasetypes'])) {
                    $data['releasetypes'] = array($data['releasetypes']);
                }

                self::$_roles[$role] = $data;
            }
        }

        closedir($dp);
        $roles = self::$_roles;
        ksort($roles);
        self::$_roles = $roles;
        self::getBaseinstallRoles(true);
        self::getInstallableRoles(true);
        self::getValidRoles('****', true);
        return true;
    }

    static function registerCustomRole($info)
    {
        if (!isset(self::$_roles)) {
            self::registerRoles();
        }

        self::$_roles[$info['name']] = $info;
        $roles = self::$_roles;
        ksort($roles);
        self::$_roles = $roles;
        self::getBaseinstallRoles(true);
        self::getInstallableRoles(true);
        self::getValidRoles('****', true);
        if (isset($info['configvar'])) {
            if (!isset($info['configvar'][0])) {
                $info['configvar'] = array($info['configvar']);
            }

            foreach ($info['configvar'] as $configvar) {
                if (($configvar['configtype'] == 'system' && in_array($configvar['name'], Config::current()->customsystemvars)) ||
                    ($configvar['configtype'] == 'user' && in_array($configvar['name'], Config::current()->customuservars)) ||
                    ($configvar['configtype'] == 'channel' && in_array($configvar['name'], Config::current()->customchannelvars))) {
                    continue;
                }

                $default = $configvar['default'];
                if (false !== strpos($default, '<?php')) {
                    $tmp = Config::current()->temp_dir . DIRECTORY_SEPARATOR . '.configdefault.php';
                    if (!file_exists(dirname($tmp))) {
                        mkdir(dirname($tmp), 0755, true);
                    }

                    if (file_put_contents($tmp, $default) === false) {
                        throw new Role\Exception("Cannot create custom role configuration file $tmp");
                    }

                    $getDefault = function() use ($tmp) {
                        include $tmp;
                        return $default;
                    };

                    $default = $getDefault();
                }

                Config::addConfigValue($configvar['name'], $default, $configvar['configtype']);
            }
        }
    }

    /**
     * Retrieve configuration information about a file role from its XML info
     *
     * @param string $role Role Classname, as in "\Pyrus\Installer\Role\Data"
     * @return array
     */
    static function getInfo($role)
    {
        if (!isset(self::$_roles)) {
            self::registerRoles();
        }

        if (empty(self::$_roles[$role])) {
            throw new Role\Exception('Unknown Role: "' . $role . '"');
        }

        return self::$_roles[$role];
    }
}
