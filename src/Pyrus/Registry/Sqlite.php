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
        if ($path && $path != ':memory:') {
            if (dirname($path) . DIRECTORY_SEPARATOR . '.pear2registry' != $path) {
                $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
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

        $sql = 'SELECT version FROM pearregistryversion';
        if (@self::$databases[$path]->singleQuery($sql) == '1.0.0') {
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

        $sql = '
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
            )';
        if (!@self::$databases[$this->_path]->queryExec($sql)) {
            self::$databases[$this->_path]->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry');
        }

        foreach ($info->allmaintainers as $role => $maintainers) {
            if (!is_array($maintainers)) continue;
            foreach ($maintainers as $maintainer) {
                $sql = '
                    INSERT INTO maintainers
                      (packages_name, packages_channel, role, name, user,
                       email, active)
                    VALUES(
                      "' . $info->name . '",
                      "' . $info->channel . '",
                      "' . $role . '",
                      "' . $maintainer['name'] . '",
                      "' . $maintainer['user'] . '",
                      "' . $maintainer['email'] . '",
                      "' . $maintainer['active'] . '"
                     )';
                if (!@self::$databases[$this->_path]->queryExec($sql)) {
                    self::$databases[$this->_path]->queryExec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
        }
        $curconfig = PEAR2_Pyrus_Config::current();
        $roles     = array();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($info->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory($info, $role)->getLocationConfig();
        }

        foreach ($info->installcontents as $file) {
            $sql = '
                INSERT INTO files
                  (packages_name, packages_channel, packagepath, role, rolepath)
                VALUES(
                  "' . $info->name . '",
                  "' . $info->channel . '",
                  "' . $file->name . '",
                  "' . $file->role . '",
                  "' . str_replace($this->_path . DIRECTORY_SEPARATOR,
                       '', $curconfig->{$roles[$file->role]}) . '"
                 )';
            if (!@self::$databases[$this->_path]->queryExec($sql)) {
                self::$databases[$this->_path]->queryExec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->channel . '/' . $info->name . ' could not be installed in registry');
            }
        }

        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($info->dependencies->$required->$package as $d) {
                    $dchannel = isset($d['channel']) ? $d['channel'] : '__uri';
                    $dmin     = isset($d['min']) ? '"' . $d['min'] . '"' : 'NULL';
                    $dmax     = isset($d['max']) ? '"' . $d['max'] . '"' : 'NULL';

                    $sql = '
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
                         )';
                    if (!@self::$databases[$this->_path]->queryExec($sql)) {
                        self::$databases[$this->_path]->queryExec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->getName() . ' could not be installed in registry');
                    }

                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }

                        foreach ($d['exclude'] as $exclude) {
                            $sql = '
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
                                 )';
                            if (!@self::$databases[$this->_path]->queryExec($sql)) {
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
                    $dchannel = isset($d['channel']) ? $d['channel'] :  '__uri';
                    $dmin     = isset($d['min']) ? '"' . $d['min'] . '"' : 'NULL';
                    $dmax     = isset($d['max']) ? '"' . $d['max'] . '"' : 'NULL';

                    $sql = '
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
                         )';
                    if (!@self::$databases[$this->_path]->queryExec($sql)) {
                        self::$databases[$this->_path]->queryExec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->name . ' could not be installed in registry');
                    }

                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }

                        foreach ($d['exclude'] as $exclude) {
                            $sql = '
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
                                 )';
                            if (!@self::$databases[$this->_path]->queryExec($sql)) {
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

        $sql = 'SELECT name FROM packages WHERE name = "' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"';
        if (!self::$databases[$this->_path]->singleQuery($sql)) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel . '/' .
                $package);
        }

        $sql = 'DELETE FROM packages WHERE name = "' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"';
        self::$databases[$this->_path]->queryExec($sql);
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
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }

        if ($field == 'date') {
            $field = 'releasedate';
        } elseif ($field == 'time') {
            $field = 'releasetime';
        } elseif ($field == 'installedfiles') {
            $ret = array();
            $sql = 'SELECT
                        rolepath, packagepath
                    FROM files
                    WHERE
                        packages_name = \'' . sqlite_escape_string($package) .'\' AND
                        packages_channel = \'' . sqlite_escape_string($channel) . '\'';
            $files = @self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);
            if (self::$databases[$this->_path]->lastError()) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                    ': ' . sqlite_error_string(self::$databases[$this->_path]->lastError()));
            }
            foreach ($files as $file) {
                $ret[] = $file['rolepath'] . DIRECTORY_SEPARATOR . $file['packagepath'];
            }

            return $ret;
        } elseif ($field == 'dirtree') {
            $ret = array();
            $sql = 'SELECT
                        rolepath, packagepath
                    FROM files
                    WHERE
                        packages_name = \'' . sqlite_escape_string($package) .'\' AND
                        packages_channel = \'' . sqlite_escape_string($channel) . '\'';
            $files = @self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);
            if (self::$databases[$this->_path]->lastError()) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                    ': ' . sqlite_error_string(self::$databases[$this->_path]->lastError()));
            }

            foreach ($files as $file) {
                $path = dirname($file['rolepath'] . DIRECTORY_SEPARATOR . $file['packagepath']);
                $ret[$path] = 1;
            }

            return $ret;
        }

        $sql = ' SELECT ' . $field . ' FROM packages WHERE
            name = \'' . sqlite_escape_string($package) . '\' AND
            channel = \'' . sqlite_escape_string($channel) . '\'';
        $info = @self::$databases[$this->_path]->singleQuery($sql, true);
        if (self::$databases[$this->_path]->lastError()) {
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
        $sql = 'SELECT name FROM packages WHERE
            channel = \'' . sqlite_escape_string($channel) . '\'
            ORDER BY name';
        foreach (self::$databases[$this->_path]->arrayQuery($sql, SQLITE_NUM) as $res) {
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
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve package file object ' .
                'for package ' . $channel . '/' . $package . ', it is not installed');
        }
        $ret = new PEAR2_Pyrus_PackageFile_v2;
        $ret->name        = $package;
        $ret->channel     = $channel;
        $ret->summary     = $this->info($package, $channel, 'summary');
        $ret->description = $this->info($package, $channel, 'description');

        $sql = 'SELECT * FROM maintainers
                WHERE
                    packages_name="' . sqlite_escape_string($package) . '" AND
                    packages_channel="' . sqlite_escape_string($channel) . '"';
        $a = self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);
        if (!$a) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $package . ', no maintainers registered');
        }
        foreach ($a as $maintainer) {
            $ret->maintainer[$maintainer['user']]
                ->name($maintainer['name'])
                ->role($maintainer['role'])
                ->email($maintainer['email'])
                ->active($maintainer['active']);
        }
        $ret->date = $this->info($package, $channel, 'date');
        // FIXME why are we querying the same info twice ?
        if ($a = $this->info($package, $channel, 'time')) {
            $ret->time = $this->info($package, $channel, 'time');
        }
        $ret->{'release-version'}  = $this->info($package, $channel, 'version');
        $ret->{'api-version'}      = $this->info($package, $channel, 'apiversion');
        $ret->stability['release'] = $this->info($package, $channel, 'stability');
        $ret->stability['api']     = $this->info($package, $channel, 'apistability');
        $uri     = $this->info($package, $channel, 'licenseuri');
        $path    = $this->info($package, $channel, 'licensepath');
        $license = $this->info($package, $channel, 'license');
        if ($uri) {
            $ret->license = array('attribs' => array('uri' => $uri), '_content' => $license);
        } elseif ($path) {
            $ret->license = array('attribs' => array('path' => $path), '_content' => $license);
        } else {
            $ret->license = $license;
        }
        $ret->notes = $this->info($package, $channel, 'releasenotes');

        $sql = 'SELECT packagepath, role FROM files
                WHERE
                    packages_name = "' . sqlite_escape_string($package) . '" AND
                    packages_channel = "' . sqlite_escape_string($channel) . '"';
        $a = self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);
        if (!$a) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $package . ', no files registered');
        }
        foreach ($a as $file) {
            $ret->files[$file['packagepath']] = array('attribs' => array('role' => $file['role']));
        }
        // these two are dummy values not based on anything
        $ret->dependencies->required->php = array('min' => phpversion());
        $ret->dependencies->required->pearinstaller = array('min' => '2.0.0');

        $sql = 'SELECT * FROM package_dependencies
                WHERE
                    packages_name = "' . sqlite_escape_string($package) . '" AND
                    packages_channel = "' . sqlite_escape_string($channel) . '"
                ORDER BY required, deppackage, depchannel, conflicts';
        $a = self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);

        $sql = 'SELECT * FROM package_dependencies_exclude
                WHERE
                    packages_name = "' . sqlite_escape_string($package) . '" AND
                    packages_channel = "' . sqlite_escape_string($channel) . '"
                ORDER BY required, deppackage, depchannel, conflicts, exclude';
        $b = self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);
        if (!$a) {
            return $ret;
        }

        $odeps = $rdeps = array();
        foreach ($a as $dep) {
            $deps = $dep['required'] ? 'rdeps' : 'odeps';
            if (isset(${$deps}[$dep['depchannel'] . '/' . $dep['deppackage']])) {
                $d = ${$deps}[$dep['depchannel'] . '/' . $dep['deppackage']];
            } else {
                $d = array();
            }
            if ($dep['min']) {
                $d['min'] = $dep['min'];
            }
            if ($dep['max']) {
                $d['max'] = $dep['max'];
            }
            if ($dep['conflicts']) {
                $d['conflicts'] = '';
            }
            if ($dep['exclude']) {
                if (!isset($d['exclude'])) {
                    $d['exclude'] = array();
                }
                $d['exclude'][] = $dep['exclude'];
            }
            ${$deps}[$dep['depchannel'] . '/' . $deps[$dep['deppackage']]] = $d;
        }

        foreach ($b as $dep) {
            $deps = $dep['required'] ? 'rdeps' : 'odeps';
            if (!isset(${$deps}[$dep['depchannel'] . '/' . $dep['deppackage']])) {
                continue;
            }

            $d = ${$deps}[$dep['depchannel'] . '/' . $dep['deppackage']];
            if (isset($d['conflicts']) && !$dep['conflicts']) {
                continue;
            } elseif (!isset($d['conflicts']) && $dep['conflicts']) {
                continue;
            }

            if ($dep['exclude']) {
                if (!isset($d['exclude'])) {
                    $d['exclude'] = array();
                }
                $d['exclude'][] = $dep['exclude'];
            }
            ${$deps}[$dep['depchannel'] . '/' . $deps[$dep['deppackage']]] = $d;
        }

        foreach ($rdeps as $dep => $info) {
            $ret->dependencies->required->package[$dep] = $info;
        }

        foreach ($odeps as $dep => $info) {
            $ret->dependencies->optional->package[$dep] = $info;
        }

        return $ret;
    }

    public function getDependentPackages(PEAR2_Pyrus_Registry_Base $package)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite channel registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT
                    packages_channel, packages_name
                FROM package_dependencies
                WHERE
                    deppackage = \'' . sqlite_escape_string($package->name) . '\' AND
                    depchannel = \'' . sqlite_escape_string($package->name) . '\'
                ORDER BY packages_channel, packages_name';
        foreach (self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC) as $res) {
            try {
                $ret[] = $this->get($res[0] . '/' . $res[1]);
            } catch (Exception $e) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Could not retrieve ' .
                    'dependent package ' . $res[0] . '/' . $res[1], $e);
            }
        }

        return $ret;
    }
}