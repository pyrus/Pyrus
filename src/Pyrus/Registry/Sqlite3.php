<?php
/**
 * PEAR2_Pyrus_Registry_Sqlite3
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
 * stored as an SQLite3 database
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
        if (!$path) {
            $path = ':memory:';
        }
    
        if (isset(static::$databases[$path]) && static::$databases[$path]) {
            return;
        }

        if (!file_exists(dirname($path))) {
            if ($readonly) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 registry, registry is read-only');
            }
            @mkdir(dirname($path), 0755, true);
        }

        @(static::$databases[$path] = new SQLite3($path));
        // ScottMac needs to fix sqlite3 FIXME
        if (static::$databases[$path]->lastErrorCode()) {
            $error = static::$databases[$path]->lastErrorMsg();
            throw new PEAR2_Pyrus_Registry_Exception('Cannot open SQLite3 registry: ' . $error);
        }

        $sql = 'SELECT version FROM pearregistryversion';
        if (@static::$databases[$path]->querySingle($sql) == '1.0.0') {
            return;
        }

        if ($readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 registry, registry is read-only');
        }

        $a = new PEAR2_Pyrus_Registry_Sqlite3_Creator;
        $a->create(static::$databases[$path]);
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

        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }

        try {
            // this ensures upgrade will work
            $this->uninstall($info->name, $info->channel);
        } catch (Exception $e) {
            // ignore errors
        }

        static::$databases[$this->_path]->exec('BEGIN');
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
            VALUES(:name, :channel, :versionrelease, :versionapi, :summary,
                :description, :stabilityrelease, :stabilityapi, :date, :time,
                :license, :licenseuri, :licensepath, :notes, :lastinstalledv,
                :lastinstalledp, :lastinstalltime
            )';

        $stmt = static::$databases[$this->_path]->prepare($sql);
        // this odd code eliminates notices
        $n = $info->name;
        $stmt->bindParam(':name',              $n);
        $c = $info->channel;
        $stmt->bindParam(':channel',           $c);
        $stmt->bindParam(':versionrelease',    $info->version['release']);
        $stmt->bindParam(':versionapi',        $info->version['api']);
        $s = $info->summary;
        $stmt->bindParam(':summary',           $s);
        $d = $info->description;
        $stmt->bindParam(':description',       $d);
        $stmt->bindParam(':stabilityrelease',  $info->stability['release']);
        $stmt->bindParam(':stabilityapi',      $info->stability['api']);
        $a = $info->date;
        $stmt->bindParam(':date',              $a);
        $stmt->bindParam(':time',              $time);
        $stmt->bindParam(':license',           $info->license['_content']);
        $stmt->bindParam(':licenseuri',        $licuri, ($licuri === null) ? SQLITE3_NULL : SQLITE3_TEXT);
        $stmt->bindParam(':licensepath',       $licpath, ($licpath === null) ? SQLITE3_NULL : SQLITE3_TEXT);
        $t = $info->notes;
        $stmt->bindParam(':notes',             $t);
        $o = null;
        $stmt->bindParam(':lastinstalledv',    $o, SQLITE3_NULL);
        $v = '2.0.0';
        $stmt->bindParam(':lastinstalledp',    $v);
        $stmt->bindParam(':lastinstalltime',   PEAR2_Pyrus_Config::configSnapshot());

        if (!$stmt->execute()) {
            static::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry: ' . static::$databases[$this->_path]->lastErrorMsg());
        }
        $stmt->close();

        $sql = '
            INSERT INTO maintainers
              (packages_name, packages_channel, role, name, user, email, active)
            VALUES
                (:name, :channel, :role, :m_name, :m_user, :m_email, :m_active)';

        $stmt = static::$databases[$this->_path]->prepare($sql);
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
                    static::$databases[$this->_path]->exec('ROLLBACK');
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

        $stmt = static::$databases[$this->_path]->prepare($sql);

        $n = $info->name;
        $c = $info->channel;
        $stmt->bindParam(':name',     $n);
        $stmt->bindParam(':channel',  $c);

        foreach ($info->installcontents as $file) {
            $rolepath = str_replace($this->_path . DIRECTORY_SEPARATOR,
                   '', $curconfig->{$roles[$file->role]});
            $stmt->bindParam(':rolepath', $rolepath);
            $p = $file->name;
            $stmt->bindParam(':path',     $p);
            $r = str_replace($this->_path . DIRECTORY_SEPARATOR,
                       '', $curconfig->{$roles[$file->role]});
            $stmt->bindParam(':role',     $r);

            if (!$stmt->execute()) {
                static::$databases[$this->_path]->exec('ROLLBACK');
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
                        static::$databases[$this->_path]->exec('ROLLBACK');
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

                        $stmt1 = static::$databases[$this->_path]->prepare($sql);
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
                                static::$databases[$this->_path]->exec('ROLLBACK');
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

        $stmt = static::$databases[$this->_path]->prepare($sql);
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

                    if (!@static::$databases[$this->_path]->exec($sql)) {
                        static::$databases[$this->_path]->exec('ROLLBACK');
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

                        $stmt1 = static::$databases[$this->_path]->prepare($sql);
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
                                static::$databases[$this->_path]->exec('ROLLBACK');
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

        static::$databases[$this->_path]->exec('COMMIT');
    }

    function uninstall($package, $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot uninstall package, registry is read-only');
        }

        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }

        $channel = PEAR2_Pyrus_Config::current()->channelregistry[$channel]->getName();
        if (!$this->exists($package, $channel)) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel . '/' .
                $package);
        }

        $sql = 'DELETE FROM packages WHERE name = "' .
              static::$databases[$this->_path]->escapeString($package) . '" AND channel = "' .
              static::$databases[$this->_path]->escapeString($channel) . '"';
        static::$databases[$this->_path]->exec($sql);
    }

    function exists($package, $channel)
    {
        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }

        $sql = 'SELECT
                    COUNT(name)
                FROM packages
                WHERE
                    name = :name AND channel = :channel
            ';
        $stmt = static::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':name',    $package);
        $stmt->bindParam(':channel', $channel);
        $result = $stmt->execute();

        if (!$result) {
            $error = static::$databases[$this->_path]->lastErrorMsg();
            throw new PEAR2_Pyrus_Registry_Exception('Cannot search for package ' . $channel . '/' . $package .
                ': ' . $error);
        }
        $ret = $result->fetchArray(SQLITE3_NUM);
        return $ret[0];
    }

    function info($package, $channel, $field)
    {
        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite3 registry for ' . $this->_path);
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

            $stmt = static::$databases[$this->_path]->prepare($sql);
            $stmt->bindParam(':name',    $package);
            $stmt->bindParam(':channel', $channel);
            $result = $stmt->execute();

            if (!$result) {
                $error = static::$databases[$this->_path]->lastErrorMsg();
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

            $stmt = static::$databases[$this->_path]->prepare($sql);
            $stmt->bindParam(':name',    $package);
            $stmt->bindParam(':channel', $channe);
            $result = $stmt->execute();

            if (!$result) {
                $error = static::$databases[$this->_path]->lastErrorMsg();
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
            name = \'' . static::$databases[$this->_path]->escapeString($package) . '\' AND
            channel = \'' . static::$databases[$this->_path]->escapeString($channel) . '\'';

        $info = @static::$databases[$this->_path]->querySingle($sql);
        if (static::$databases[$this->_path]->lastErrorCode()) {
            $error = static::$databases[$this->_path]->lastErrorMsg();
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
        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT name FROM packages WHERE channel = :channel ORDER BY name';
        $stmt = static::$databases[$this->_path]->prepare($sql);
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
        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite3 registry for ' . $this->_path);
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

        $stmt = static::$databases[$this->_path]->prepare($sql);
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

        $stmt = static::$databases[$this->_path]->prepare($sql);
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
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"
                ORDER BY required, deppackage, depchannel, conflicts';
        $a = static::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);

        $sql = 'SELECT * FROM package_dependencies_exclude
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"
                ORDER BY required, deppackage, depchannel, conflicts, exclude';
        $b = static::$databases[$this->_path]->arrayQuery($sql, SQLITE_ASSOC);
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
        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT
                    packages_channel, packages_name
                FROM package_dependencies
                WHERE
                    deppackage = :name AND depchannel = :name
                ORDER BY packages_channel, packages_name';
        $stmt = static::$databases[$this->_path]->prepare($sql);
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