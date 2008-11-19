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
 * @author    Helgi Þormar Þorbjörnsson <helgi@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Sqlite3 extends PEAR2_Pyrus_Registry_Base
{
    /**
     * The database resources, stored by path
     *
     * This allows singleton access to the database by separate objects
     * @var SQLite3
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

        self::$databases[$path] = new SQLite3($path, 0666);
        if (!self::$databases[$path]) {
            $error = self::$databases[$path]->lastErrorMsg();
            throw new PEAR2_Pyrus_Registry_Exception('Cannot open SQLite registry: ' . $error);
        }

        $sql = 'SELECT version FROM pearregistryversion';
        if (@self::$databases[$path]->querySingle($sql) == '1.0.0') {
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

        self::$databases[$this->_path]->exec('BEGIN');
        $licloc = $info->license;
        $licuri = isset($licloc['attribs']['uri']) ? $licloc['attribs']['uri'] : null;
        $licpath = isset($licloc['attribs']['path']) ? $licloc['attribs']['path'] : null;
        $time = ($info->time ? $info->time : null);

        $sql = '
            INSERT INTO packages
              (name, channel, version, apiversion, summary,
               description, stability, apistability, releasedate,
               releasetime, license, licenseuri, licensepath,
               releasenotes, lastinstalledversion, installedwithpear,
               installtimeconfig)
            VALUES(:name, :channel, :version-release, :version-api, :summary,
                :description, :stability-release, :stability-api, :date, :time,
                :license, :license-uri, :license-path, :notes, :lastinstalledv,
                :lastinstalledp, :lastinstalltime
            )';

        $stmt = @self::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':name',              $info->name);
        $stmt->bindParam(':channel',           $info->channel);
        $stmt->bindParam(':version-release',   $info->version['release']);
        $stmt->bindParam(':version-api',       $info->version['api']);
        $stmt->bindParam(':summary',           $info->summary);
        $stmt->bindParam(':desc',              $info->description);
        $stmt->bindParam(':stability-release', $info->stability['release']);
        $stmt->bindParam(':stability-api',     $info->stability['api']);
        $stmt->bindParam(':date',              $info->date);
        $stmt->bindParam(':time',              $time);
        $stmt->bindParam(':license',           $info->license['_content']);
        $stmt->bindParam(':license-uri',       $licuri);
        $stmt->bindParam(':license-path',      $licpath);
        $stmt->bindParam(':notes',             $info->notes);
        $stmt->bindParam(':lastinstalledv',    null);
        $stmt->bindParam(':lastinstalledp',    '2.0.0');
        $stmt->bindParam(':lastinstalltime',   PEAR2_Pyrus_Config::configSnapshot());

        if (!$stmt->execute()) {
            self::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry');
        }
        $stmt->close();

        $sql = '
            INSERT INTO maintainers
              (packages_name, packages_channel, role, name, user, email, active)
            VALUES
                (:name, :channel, :role, :m_name, :m_user, :m_email, :m_active)';

        $stmt = self::$databases[$this->_path]->prepare($sql);
        foreach ($info->allmaintainers as $role => $maintainers) {
            if (!is_array($maintainers)) {
                continue;
            }

            foreach ($maintainers as $maintainer) {
                $stmt->clear();
                $stmt->bindParam(':name',     $info->name);
                $stmt->bindParam(':channel',  $info->channel);
                $stmt->bindParam(':role',     $role);
                $stmt->bindParam(':m_name',   $maintainer['name']);
                $stmt->bindParam(':m_user',   $maintainer['user']);
                $stmt->bindParam(':m_email',  $maintainer['email']);
                $stmt->bindParam(':m_active', $maintainer['active']);

                if (!$stmt->execute()) {
                    self::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
        }
        $stmt->close();

        $curconfig = PEAR2_Pyrus_Config::current();
        $roles     = array();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($info->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] = PEAR2_Pyrus_Installer_Role::factory($info, $role)->getLocationConfig();
        }

        $sql = '
            INSERT INTO files
              (packages_name, packages_channel, packagepath, role, rolepath)
            VALUES(:name, :channel, :path, :role, :rolepath)';

        $stmt = self::$databases[$this->_path]->prepare($sql);

        $stmt->bindParam(':name',     $info->name);
        $stmt->bindParam(':channel',  $info->channel);
        $stmt->bindParam(':path',     $info->name);
        $stmt->bindParam(':role',     $info->role);

        foreach ($info->installcontents as $file) {
            $rolepath = str_replace($this->_path . DIRECTORY_SEPARATOR,
                   '', $curconfig->{$roles[$file->role]});
            $stmt->bindParam(':rolepath', $rolepath);

            if (!$stmt->execute()) {
                self::$databases[$this->_path]->exec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->channel . '/' . $info->name . ' could not be installed in registry');
            }
        }
        $stmt->close();

        $sql = '
            INSERT INTO package_dependencies
                (required, packages_name, packages_channel, deppackage,
                 depchannel, conflicts, min, max)
            VALUES
                (:required, :name, :channel, :dep_package, :dep_channel,
                 :conflicts, :min, :max)';

        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($info->dependencies->$required->$package as $d) {
                    $dchannel = isset($d['channel']) ? $d['channel'] : '__uri';
                    $dmin     = isset($d['min']) ? $d['min'] : null;
                    $dmax     = isset($d['max']) ? $d['max'] : null;

                    $stmt->clear();
                    $stmt->bindParam(':required', ($required == 'required' ? 1 : 0), SQLITE3_INTEGER);
                    $stmt->bindParam(':name', $info->name);
                    $stmt->bindParam(':channel', $info->channel);
                    $stmt->bindParam(':dep_package', $d['name']);
                    $stmt->bindParam(':dep_channel', $dchannel);
                    $stmt->bindParam(':conflicts', isset($d['conflicts']), SQLITE3_INTEGER);
                    $stmt->bindParam(':min', $dmin);
                    $stmt->bindParam(':max', $dmax);

                    if (!$stmt->execute()) {
                        self::$databases[$this->_path]->exec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->getName() . ' could not be installed in registry');
                    }

                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }

                        $sql = '
                            INSERT INTO package_dependencies_exclude
                             (required, packages_name, packages_channel,
                              deppackage, depchannel, exclude, conflicts)
                            VALUES(:required, :name, :channel, :dep_package,
                                :dep_channel, :exclude, :conflicts)';

                        $stmt1 = self::$databases[$this->_path]->prepare($sql);
                        foreach ($d['exclude'] as $exclude) {
                            $stmt1->clear();
                            $stmt1->bindParam(':required', ($required == 'required' ? 1 : 0), SQLITE3_INTEGER);
                            $stmt1->bindParam(':name', $info->name);
                            $stmt1->bindParam(':channel', $info->channel);
                            $stmt1->bindParam(':dep_package', $d['name']);
                            $stmt1->bindParam(':dep_channel', $dchannel);
                            $stmt1->bindParam(':exclude', $exclude);
                            $stmt1->bindParam(':conflicts', isset($d['conflicts']), SQLITE3_INTEGER);

                            if (!$stmt1->execute()) {
                                self::$databases[$this->_path]->exec('ROLLBACK');
                                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                    $info->channel . '/' . $info->getName() . ' could not be installed in registry');
                            }
                        }
                        $stmt1->close();
                    }
                }
            }
        }
        $stmt->close();

        $sql = '
            INSERT INTO package_dependencies
              (required, packages_name, packages_channel, deppackage,
               depchannel, conflicts, min, max)
            VALUES
                (0, :name, :channel, :dep_package, :dep_channel, :conflitcs, :min, :max)';

        $stmt = self::$databases[$this->_path]->prepare($sql);
        foreach ($info->dependencies->group as $group) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($group->$package as $d) {
                    $dchannel = isset($d['channel']) ? $d['channel'] :  '__uri';
                    $dmin     = isset($d['min']) ? $d['min'] : null;
                    $dmax     = isset($d['max']) ? $d['max'] : null;

                    $stmt->clear();
                    $stmt->bindParam(':name', $info->name);
                    $stmt->bindParam(':channel', $info->channel);
                    $stmt->bindParam(':dep_package', $d['name,']);
                    $stmt->bindParam(':dep_channel', $dchannel);
                    $stmt->bindParam(':conflitcs', isset($d['conflicts']), SQLITE3_INTEGER);
                    $stmt->bindParam(':min', $dmin);
                    $stmt->bindParam(':max', $dmax);

                    if (!@self::$databases[$this->_path]->exec($sql)) {
                        self::$databases[$this->_path]->exec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->name . ' could not be installed in registry');
                    }

                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }

                        $sql = '
                            INSERT INTO package_dependencies_exclude
                             (required, packages_name, packages_channel,
                              deppackage, depchannel, exclude, conflicts)
                            VALUES(0, :name, :channel, :dep_package,
                                :dep_channel, :exclude, :conflicts)';

                        $stmt1 = self::$databases[$this->_path]->prepare($sql);
                        foreach ($d['exclude'] as $exclude) {
                            $stmt1->clear();
                            $stmt1->bindParam(':required', ($required == 'required' ? 1 : 0), SQLITE3_INTEGER);
                            $stmt1->bindParam(':name',        $info->name);
                            $stmt1->bindParam(':channel',     $info->channel);
                            $stmt1->bindParam(':dep_package', $d['name']);
                            $stmt1->bindParam(':dep_channel', $dchannel);
                            $stmt1->bindParam(':exclude',     $exclude);
                            $stmt1->bindParam(':conflicts',   isset($d['conflicts']), SQLITE3_INTEGER);

                            if (!$stmt1->execute()) {
                                self::$databases[$this->_path]->exec('ROLLBACK');
                                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                    $info->channel . '/' . $info->name . ' could not be installed in registry');
                            }
                        }
                        $stmt1->close();
                    }
                }
            }
        }
        $stmt->close();

        self::$databases[$this->_path]->exec('COMMIT');
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
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel . '/' .
                $package);
        }

        $sql = 'DELETE FROM packages WHERE name = "' .
              self::$databases[$this->_path]->escapeString($package) . '" AND channel = "' .
              self::$databases[$this->_path]->escapeString($channel) . '"';
        self::$databases[$this->_path]->exec($sql);
    }

    function exists($package, $channel)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }

        $sql = 'SELECT COUNT(name) FROM packages WHERE ' .
            'name = "' . self::$databases[$this->_path]->escapeString($package) . '" AND channel = "' .
            self::$databases[$this->_path]->escapeString($channel) . '"';
        return self::$databases[$this->_path]->singleQuery($sql);
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
                        packages_name = :name AND packages_channel = :channel';

            $stmt = self::$databases[$this->_path]->prepare($sql);
            $stmt->bindParam(':name',    $package);
            $stmt->bindParam(':channel', $channel);
            $result = $stmt->execute();

            if (!$result) {
                $error = self::$databases[$this->_path]->lastErrorMsg();
                throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                    ': ' . $error);
            }

            foreach ($result->fetchArray(SQLITE3_ASSOC) as $file) {
                $ret[] = $file['rolepath'] . DIRECTORY_SEPARATOR . $file['packagepath'];
            }
            $stmt->close();

            return $ret;
        } elseif ($field == 'dirtree') {
            $ret = array();
            $sql = 'SELECT
                        rolepath, packagepath
                    FROM files
                    WHERE
                        packages_name = :name AND packages_channel = :channel';

            $stmt = self::$databases[$this->_path]->prepare($sql);
            $stmt->bindParam(':name',    $package);
            $stmt->bindParam(':channel', $channe);
            $result = $stmt->execute();

            if (!$result) {
                $error = self::$databases[$this->_path]->lastErrorMsg();
                throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                    ': ' . $error);
            }

            foreach ($result->fetchArray(SQLITE3_ASSOC) as $file) {
                $path = dirname($file['rolepath'] . DIRECTORY_SEPARATOR . $file['packagepath']);
                $ret[$path] = 1;
            }
            $stmt->close();

            return $ret;
        }

        $sql = ' SELECT ' . $field . ' FROM packages WHERE
            name = \'' . self::$databases[$this->_path]->escapeString($package) . '\' AND
            channel = \'' . self::$databases[$this->_path]->escapeString($channel) . '\'';

        $info = @self::$databases[$this->_path]->singleQuery($sql, true);
        if (self::$databases[$this->_path]->lastErrorCode()) {
            $error = self::$databases[$this->_path]->lastErrorMsg();
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                ': ' . $error);
        }

        return $info;
    }

    /**
     * List all packages in a given channel
     *
     * @param string $channel name of the channel being queried
     *
     * @return array One dimensional array with the package name as value
     */
    public function listPackages($channel)
    {
        if (!isset(self::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT name FROM packages WHERE channel = :channel ORDER BY name';
        $stmt = self::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':channel', $channel);
        $result = $stmt->execute();

        foreach ($result->fetchArray(SQLITE3_NUM) as $res) {
            $ret[] = $res[0];
        }

        return $ret;
    }

    function __get($var)
    {
        if ($var === 'package') {
            return new PEAR2_Pyrus_Registry_Sqlite3_Package($this);
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
                WHERE packages_name = :name AND packages_channel = :channel';

        $stmt = self::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':name',    $package);
        $stmt->bindParam(':channel', $channel);
        $result = $stmt->prepare();

        if (!$result) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $package . ', no maintainers registered');
        }

        foreach ($result->fetchArray(SQLITE3_ASSOC) as $maintainer) {
            $ret->maintainer[$maintainer['user']]
                ->name($maintainer['name'])
                ->role($maintainer['role'])
                ->email($maintainer['email'])
                ->active($maintainer['active']);
        }
        $stmt->close();

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
                WHERE packages_name = :name AND packages_channel = :channel';

        $stmt = self::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':name',    $package);
        $stmt->bindParam(':channel', $channel);
        $result = $stmt->prepare();

        if (!$result) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $package . ', no files registered');
        }

        foreach ($result->fetchArray(SQLITE3_ASSOC) as $file) {
            $ret->files[$file['packagepath']] = array('attribs' => array('role' => $file['role']));
        }
        $stmt->close();

        // these two are dummy values not based on anything
        $ret->dependencies->required->php = array('min' => phpversion());
        $ret->dependencies->required->pearinstaller = array('min' => '2.0.0');

        $sql = 'SELECT * FROM package_dependencies
                WHERE
                    packages_name = "' . self::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . self::$databases[$this->_path]->escapeString($channel) . '"
                ORDER BY required, deppackage, depchannel, conflicts';
        $a = self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);

        $sql = 'SELECT * FROM package_dependencies_exclude
                WHERE
                    packages_name = "' . self::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . self::$databases[$this->_path]->escapeString($channel) . '"
                ORDER BY required, deppackage, depchannel, conflicts, exclude';
        $b = self::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);
        if (!$a) {
            return $ret;
        }

        //FIXME see about refactoring these two into a function or such
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
                    deppackage = :name AND depchannel = :name
                ORDER BY packages_channel, packages_name';
        $stmt = self::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':name', $package->name, SQLITE3_TEXT);
        $result = $stmt->execute();

        foreach ($result->fetchArray(SQLITE_ASSOC) as $res) {
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