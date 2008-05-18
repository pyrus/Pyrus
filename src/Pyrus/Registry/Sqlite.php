<?php
/**
 * PEAR2_Pyrus_Registry_Sqlite
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
 * This is the central registry, that is used for all installer options,
 * stored as an SQLite database
 *
 * Registry information that must be stored:
 *
 * - A list of installed packages
 * - the files in each package
 * - known channels
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Sqlite extends PEAR2_Pyrus_Registry_Base
{
    /**
     * The database resources, stored by path
     *
     * This allows singleton access to the database by separate objects
     * @var SQLiteDatabase
     */
    static protected $databases = array();
    private $_path;
    protected $readonly;

    /**
     * Initialize the registry
     *
     * @param unknown_type $path
     */
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        if ($path) {
            if ($path != ':memory:') {
                if (dirname($path) . DIRECTORY_SEPARATOR . '.pear2registry' != $path) {
                    $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
                }
            }
        }
        $this->_init($path, $readonly);
        $this->_path = $path;
    }

    private function _init($path, $readonly)
    {
        if (isset(self::$databases[$path]) && self::$databases[$path]) {
            return;
        }

        $error = '';
        if (!$path) {
            $path = ':memory:';
        } elseif (!file_exists(dirname($path))) {
            if ($readonly) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite registry, registry is read-only');
            }
            @mkdir(dirname($path), 0755, true);
        }
        if ($readonly && !file_exists($path)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite registry, registry is read-only');
        }
        self::$databases[$path] = new SQLiteDatabase($path, 0666, $error);
        if (!self::$databases[$path]) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot open SQLite registry: ' . $error);
        }
        if (@self::$databases[$path]->singleQuery('SELECT version FROM pearregistryversion') == '1.0.0') {
            return;
        }
        $a = new PEAR2_Pyrus_Registry_Sqlite_Creator;
        $a->create(self::$databases[$path]);
    }

    function getDatabase()
    {
        return $this->_path;
    }

    /**
     * Add an installed package to the registry
     *
     * @param PEAR2_Pyrus_PackageFile_v2 $info
     */
    function install(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot install package, registry is read-only');
        }
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }
        try {
            // this ensures upgrade will work
            $this->uninstall($info->name, $info->channel);
        } catch (Exception $e) {
            // ignore errors
        }
        self::$databases[$this->_path]->queryExec('BEGIN');
        $licloc = $info->license;
        $licuri = isset($licloc['attribs']['uri']) ? '"' .
            sqlite_escape_string($licloc['attribs']['uri']) . '"' : 'NULL';
        $licpath = isset($licloc['attribs']['path']) ? '"' .
            sqlite_escape_string($licloc['attribs']['path']) . '"' : 'NULL';
        if (!@self::$databases[$this->_path]->queryExec('
             INSERT INTO packages
              (name, channel, version, apiversion, summary,
               description, stability, apistability, releasedate,
               releasetime, license, licenseuri, licensepath,
               releasenotes, lastinstalledversion, installedwithpear,
               installtimeconfig)
             VALUES(
              "' . $info->name . '",
              "' . $info->channel . '",
              "' . $info->version['release'] . '",
              "' . $info->version['api'] . '",
              \'' . sqlite_escape_string($info->summary) . '\',
              \'' . sqlite_escape_string($info->description) . '\',
              "' . $info->stability['release'] . '",
              "' . $info->stability['api'] . '",
              "' . $info->date . '",
              ' . ($info->time ? '"' . $info->time . '"' : 'NULL') . ',
              "' . $info->license['_content'] . '",
              ' . $licuri . ',
              ' . $licpath . ',
              \'' . sqlite_escape_string($info->notes) . '\',
              NULL,
              "2.0.0",
              "' . PEAR2_Pyrus_Config::configSnapshot() . '"
             )
            ')) {
            self::$databases[$this->_path]->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry');
        }
        foreach ($info->allmaintainers as $role => $maintainers) {
            if (!is_array($maintainers)) continue;
            foreach ($maintainers as $maintainer) {
                if (!@self::$databases[$this->_path]->queryExec('
                     INSERT INTO maintainers
                      (packages_name, packages_channel, role, user,
                       email, active)
                     VALUES(
                      "' . $info->name . '",
                      "' . $info->channel . '",
                      "' . $role . '",
                      "' . $maintainer['user'] . '",
                      "' . $maintainer['email'] . '",
                      "' . $maintainer['active'] . '"
                     )
                    ')) {
                    self::$databases[$this->_path]->queryExec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
        }
        $curconfig = PEAR2_Pyrus_Config::current();
        $roles = array();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($info->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory($info, $role)->getLocationConfig();
        }
        foreach ($info->installcontents as $file) {
            if (!@self::$databases[$this->_path]->queryExec('
                 INSERT INTO files
                  (packages_name, packages_channel, packagepath, role, rolepath)
                 VALUES(
                  "' . $info->name . '",
                  "' . $info->channel . '",
                  "' . $file->name . '",
                  "' . $file->role . '",
                  "' . str_replace($this->_path . DIRECTORY_SEPARATOR,
                       '', $curconfig->{$roles[$file->role]}) . '"
                 )
                ')) {
                self::$databases[$this->_path]->queryExec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->channel . '/' . $info->name . ' could not be installed in registry');
            }
        }

        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($info->dependencies->$required->$package as $d) {
                    $dchannel = isset($d['channel']) ?
                        $d['channel'] :
                        '__uri';
                    $dmin = isset($d['min']) ?
                        '"' . $d['min'] . '"':
                        'NULL';
                    $dmax = isset($d['max']) ?
                        '"' . $d['max'] . '"':
                        'NULL';
                    if (!@self::$databases[$this->_path]->queryExec('
                         INSERT INTO package_dependencies
                          (required, packages_name, packages_channel, deppackage,
                           depchannel, conflicts, min, max)
                         VALUES(
                          ' . ($required == 'required' ? 1 : 0) . ',
                          "' . $info->name . '",
                          "' . $info->channel . '",
                          "' . $d['name'] . '",
                          "' . $dchannel . '",
                          "' . isset($d['conflicts']) . '",
                          ' . $dmin . ',
                          ' . $dmax . '
                         )
                        ')) {
                        self::$databases[$this->_path]->queryExec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->getName() . ' could not be installed in registry');
                    }
                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }
                        foreach ($d['exclude'] as $exclude) {
                            if (!@self::$databases[$this->_path]->queryExec('
                                 INSERT INTO package_dependencies_exclude
                                  (required, packages_name, packages_channel,
                                   deppackage, depchannel, exclude, conflicts)
                                 VALUES(
                                  ' . ($required == 'required' ? 1 : 0) . ',
                                  "' . $info->name . '",
                                  "' . $info->channel . '",
                                  "' . $d['name'] . '",
                                  "' . $dchannel . '",
                                  "' . $exclude . '",
                                  "' . isset($d['conflicts']) . '"
                                 )
                                ')) {
                                self::$databases[$this->_path]->queryExec('ROLLBACK');
                                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                    $info->channel . '/' . $info->getName() . ' could not be installed in registry');
                            }
                        }
                    }
                }
            }
        }
        foreach ($info->dependencies->group as $group) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($group->$package as $d) {
                    $dchannel = isset($d['channel']) ?
                        $d['channel'] :
                        '__uri';
                    $dmin = isset($d['min']) ?
                        '"' . $d['min'] . '"':
                        'NULL';
                    $dmax = isset($d['max']) ?
                        '"' . $d['max'] . '"':
                        'NULL';
                    if (!@self::$databases[$this->_path]->queryExec('
                         INSERT INTO package_dependencies
                          (required, packages_name, packages_channel, deppackage,
                           depchannel, conflicts, min, max)
                         VALUES(
                          0,
                          "' . $info->name . '",
                          "' . $info->channel . '",
                          "' . $d['name'] . '",
                          "' . $dchannel . '",
                          "' . isset($d['conflicts']) . '",
                          ' . $dmin . ',
                          ' . $dmax . '
                         )
                        ')) {
                        self::$databases[$this->_path]->queryExec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->name . ' could not be installed in registry');
                    }
                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }
                        foreach ($d['exclude'] as $exclude) {
                            if (!@self::$databases[$this->_path]->queryExec('
                                 INSERT INTO package_dependencies_exclude
                                  (required, packages_name, packages_channel,
                                   deppackage, depchannel, exclude, conflicts)
                                 VALUES(
                                  0,
                                  "' . $info->name . '",
                                  "' . $info->channel . '",
                                  "' . $d['name'] . '",
                                  "' . $dchannel . '",
                                  "' . $exclude . '",
                                  "' . isset($d['conflicts']) . '",
                                 )
                                ')) {
                                self::$databases[$this->_path]->queryExec('ROLLBACK');
                                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                    $info->channel . '/' . $info->name . ' could not be installed in registry');
                            }
                        }
                    }
                }
            }
        }
        self::$databases[$this->_path]->queryExec('COMMIT');
    }

    function uninstall($package, $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot uninstall package, registry is read-only');
        }
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }
        $channel = PEAR2_Pyrus_Config::current()->channelregistry[$channel]->getName();
        if (!self::$databases[$this->_path]->singleQuery('SELECT name FROM packages WHERE name="' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"')) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel . '/' .
                $package);
        }
        self::$databases[$this->_path]->queryExec('DELETE FROM packages WHERE name="' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"');
    }

    function exists($package, $channel)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }
        return self::$databases[$this->_path]->singleQuery('SELECT COUNT(*) FROM packages WHERE ' .
            'name=\'' . sqlite_escape_string($package) . '\' AND channel=\'' .
            sqlite_escape_string($channel) . '\'');
    }

    function info($package, $channel, $field)
    {
        $info = @self::$databases[$this->_path]->singleQuery('
            SELECT ' . $field . ' FROM packages WHERE
            name = \'' . sqlite_escape_string($package) . '\' AND
            channel = \'' . sqlite_escape_string($channel) . '\'', true);
        if (!$info) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                ': ' . sqlite_error_string(self::$databases[$this->_path]->lastError()));
        }
        return $info;
    }

    function listPackages($channel)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }
        $ret = array();
        foreach (self::$databases[$this->_path]->arrayQuery('SELECT name FROM packages WHERE
            channel = \'' . sqlite_escape_string($channel) . '\'
            ORDER BY name
        ', SQLITE_NUM) as $res) {
            $ret[] = $res[0];
        }
        return $ret;
    }

    function __get($var)
    {
        if ($var === 'package') {
            return new PEAR2_Pyrus_Registry_Sqlite_Package($this);
        }
    }

    /**
     * Extract a packagefile object from the registry
     * @return PEAR2_Pyrus_PackageFile_v2
     */
    function toPackageFile($package, $channel)
    {
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve package file object ' .
                'for package ' . $package . '/' . $channel . ', it is not installed');
        }
        $ret = new PEAR2_Pyrus_PackageFile_v2;
        $ret->name = $package;
        $ret->channel = $channel;
        $ret->summary = $this->info($package, $channel, 'summary');
        $ret->description = $this->info($package, $channel, 'description');
        // maintainers
        // date
        // version
        // stability
        // license
        // notes
        // dependencies
        // filelist
    }
}
