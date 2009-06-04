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
        $this->_path = $path ? $path : ':memory:';
    }

    private function _init($path, $readonly)
    {
        if (!$path) {
            $path = ':memory:';
        }
    
        if (isset(static::$databases[$path]) && static::$databases[$path]) {
            return;
        }

        $dbpath = $path;
        if ($path != ':memory:' && isset(PEAR2_Pyrus::$options['packagingroot'])) {
            $dbpath = PEAR2_Pyrus::prepend(PEAR2_Pyrus::$options['packagingroot'], $path);
        }

        if ($path != ':memory:' && !file_exists(dirname($dbpath))) {
            if ($readonly) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
            }
            @mkdir(dirname($dbpath), 0755, true);
        }

        if ($readonly && $path != ':memory:' && !file_exists($dbpath)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
        }

        static::$databases[$path] = new SQLite3($dbpath);
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
        try {
            $a->create(static::$databases[$path]);
        } catch (Exception $e) {
            unset(static::$databases[$path]);
            $a = get_class($e);
            throw new $a('Database initialization failed', 0, $e);
        }
    }

    function getDatabase()
    {
        return $this->_path;
    }

    /**
     * Add an installed package to the registry
     *
     * @param PEAR2_Pyrus_IPackageFile $info
     */
    function install(PEAR2_Pyrus_IPackageFile $info, $replace = false)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot install package, registry is read-only');
        }

        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }

        try {
            // this ensures upgrade will work
            static::$databases[$this->_path]->exec('BEGIN');
            $this->uninstall($info->name, $info->channel);
        } catch (Exception $e) {
            // ignore errors
        }

        if (!$replace) {
            $info = $info->toRaw();
            // this avoids potential exception on setting date/time
            // which can happen if $info is a registry package that
            // has been uninstalled
            $info->date = date('Y-m-d');
            $info->time = date('H:i:s');
        }

        $licloc = $info->license;
        $licuri = $info->license['uri'];
        $licpath = $info->license['path'];
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
        $rv = $info->version['release'];
        $stmt->bindParam(':versionrelease',    $rv);
        $av = $info->version['api'];
        $stmt->bindParam(':versionapi',        $av);
        $s = $info->summary;
        $stmt->bindParam(':summary',           $s);
        $d = $info->description;
        $stmt->bindParam(':description',       $d);
        $rs = $info->stability['release'];
        $stmt->bindParam(':stabilityrelease',  $rs);
        $ra = $info->stability['api'];
        $stmt->bindParam(':stabilityapi',      $ra);
        $a = $info->date;
        $stmt->bindParam(':date',              $a);
        $stmt->bindParam(':time',              $time);
        $lname = $info->license['name'];
        $stmt->bindParam(':license',           $lname);
        $stmt->bindParam(':licenseuri',        $licuri, ($licuri === null) ? SQLITE3_NULL : SQLITE3_TEXT);
        $stmt->bindParam(':licensepath',       $licpath, ($licpath === null) ? SQLITE3_NULL : SQLITE3_TEXT);
        $t = $info->notes;
        $stmt->bindParam(':notes',             $t);
        $o = null;
        $stmt->bindParam(':lastinstalledv',    $o, SQLITE3_NULL);
        $v = '2.0.0';
        $stmt->bindParam(':lastinstalledp',    $v);
        $configsnapshot = PEAR2_Pyrus_Config::configSnapshot();
        $stmt->bindParam(':lastinstalltime',   $configsnapshot);

        if (!@$stmt->execute()) {
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
        $n = $info->name;
        $c = $info->channel;
        foreach ($info->allmaintainers as $role => $maintainers) {
            foreach ($maintainers as $maintainer) {
                $stmt->clear();
                $stmt->bindParam(':name',     $n);
                $stmt->bindParam(':channel',  $c);
                $stmt->bindParam(':role',     $role);
                $mn = $maintainer->name;
                $stmt->bindParam(':m_name',   $mn);
                $mu = $maintainer->user;
                $stmt->bindParam(':m_user',   $mu);
                $me = $maintainer->email;
                $stmt->bindParam(':m_email',  $me);
                $ma = $maintainer->active;
                $stmt->bindParam(':m_active', $ma);

                if (!@$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
        }
        $stmt->close();

        $curconfig = PEAR2_Pyrus_Config::current();
        $roles     = array();

        $sql = '
            INSERT INTO configureoptions
              (packages_name, packages_channel, name, prompt, defaultValue)
            VALUES(:name, :channel, :oname, :prompt, :default)';

        $stmt = static::$databases[$this->_path]->prepare($sql);

        $stmt->bindValue(':name',     $n);
        $stmt->bindValue(':channel',  $c);
        
        foreach ($info->configureoption as $option) {
            $stmt->bindValue(':oname', $option->name);
            $stmt->bindValue(':prompt', $option->prompt);
            if ($option->default === null) {
                $stmt->bindValue(':default', null, SQLITE3_NULL);
            } else {
                $stmt->bindValue(':default', $option->default);
            }
        }

        $sql = '
            INSERT INTO files
              (packages_name, packages_channel, packagepath, configpath, role,
               relativepath, origpath, baseinstalldir, tasks, md5sum)
            VALUES(:name, :channel, :path, :configpath, :role, :relativepath, :origpath, :baseinstall, :tasks, :md5)';

        $stmt = static::$databases[$this->_path]->prepare($sql);

        $stmt->bindParam(':name',     $n);
        $stmt->bindParam(':channel',  $c);
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($info->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory($info->getPackageType(), $role);
        }

        foreach ($info->installcontents as $file) {
            $relativepath = $roles[$file->role]->getRelativeLocation($info, $file);
            if (!$relativepath) {
                continue;
            }

            $stmt->bindParam(':relativepath', $relativepath);
            $p = $curconfig->{$roles[$file->role]->getLocationConfig()};
            $stmt->bindParam(':configpath',         $p);
            $stmt->bindValue(':path', $p . DIRECTORY_SEPARATOR . $relativepath);
            $o = $file['attribs']['name'];
            $stmt->bindParam(':origpath',     $o);
            $r = $file->role;
            $stmt->bindParam(':role',         $r);
            $bi = $file->baseinstalldir;
            $stmt->bindParam(':baseinstall',  $bi);
            $stmt->bindValue(':tasks',        serialize($file->tasks));
            if ($file->md5sum) {
                $stmt->bindValue(':md5', $file->md5sum);
            } else {
                // clearly the person installing doesn't care about this, so
                // use a dummy value
                $stmt->bindValue(':md5', md5(''));
            }

            if (!@$stmt->execute()) {
                static::$databases[$this->_path]->exec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->channel . '/' . $info->name . ' could not be installed in registry');
            }
        }
        $stmt->close();

        $sql = '
            INSERT INTO baseinstalldirs
              (packages_name, packages_channel, dirname, baseinstall)
            VALUES(:name, :channel, :dirname, :baseinstall)';

        $stmt = static::$databases[$this->_path]->prepare($sql);

        foreach ($info->getBaseInstallDirs() as $dir => $base) {
            $stmt->bindParam(':name',        $n);
            $stmt->bindParam(':channel',     $c);
            $stmt->bindParam(':dirname',     $dir);
            $stmt->bindParam(':baseinstall', $base);

            if (!@$stmt->execute()) {
                static::$databases[$this->_path]->exec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->channel . '/' . $info->name . ' could not be installed in registry');
            }
        }
        $stmt->close();

        if (count($info->compatible)) {
            $sql = '
                INSERT INTO compatible_releases
                    (packages_name, packages_channel,
                     compat_package, compat_channel, min, max)
                VALUES
                    (:name, :channel, :cname, :cchannel, :min, :max)';
            $stmt = static::$databases[$this->_path]->prepare($sql);

            $stmt->bindValue(':name', $n);
            $stmt->bindValue(':channel', $c);

            $sql2 = '
                INSERT INTO compatible_releases_exclude
                    (packages_name, packages_channel,
                     compat_package, compat_channel, exclude)
                VALUES
                    (:name, :channel, :cname, :cchannel, :exclude)';
            $stmt2 = static::$databases[$this->_path]->prepare($sql2);

            $stmt2->bindValue(':name', $n);
            $stmt2->bindValue(':channel', $c);
            foreach ($info->compatible as $compatible) {
                $stmt->bindValue(':cname', $compatible->name);
                $stmt->bindValue(':cchannel', $compatible->channel);
                $stmt->bindValue(':min', $compatible->min);
                $stmt->bindValue(':max', $compatible->max);
                if (!@$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
                if (isset($compatible->exclude)) {
                    $stmt2->bindValue(':cname', $compatible->name);
                    $stmt2->bindValue(':cchannel', $compatible->channel);
                    foreach ($compatible->exclude as $exclude) {
                        $stmt2->bindValue(':exclude', $exclude);
                        if (!@$stmt2->execute()) {
                            static::$databases[$this->_path]->exec('ROLLBACK');
                            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                $info->channel . '/' . $info->name . ' could not be installed in registry');
                        }
                    }
                }
            }
        }

        $sql = '
            INSERT INTO extension_dependencies
                (required, packages_name, packages_channel, extension,
                 conflicts, min, max, recommended)
            VALUES
                (:required, :name, :channel, :extension,
                 :conflicts, :min, :max, :recommended)';
        $stmt = static::$databases[$this->_path]->prepare($sql);
        $first = true;
        foreach (array('required', 'optional') as $required) {
            foreach ($info->dependencies[$required]->extension as $d) {
                // $d is a PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object
                $dmin         = $d->min;
                $dmax         = $d->max;
                $drecommended = $d->recommended;
                $ext          = $d->name;

                if (!$first) {
                    $stmt->clear();
                    $first = false;
                }
                $req = ($required == 'required' ? 1 : 0);
                $stmt->bindParam(':required', $req, SQLITE3_INTEGER);
                $stmt->bindParam(':name', $n);
                $stmt->bindParam(':channel', $c);
                $stmt->bindParam(':extension', $ext);
                $con = $d->conflicts;
                $stmt->bindParam(':conflicts', $con, SQLITE3_INTEGER);
                $stmt->bindParam(':min', $dmin);
                $stmt->bindParam(':max', $dmax);
                $stmt->bindParam(':recommended', $drecommended);

                if (!@$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }

                if (isset($d->exclude)) {
                    $sql = '
                        INSERT INTO extension_dependencies_exclude
                         (required, packages_name, packages_channel,
                          extension, exclude, conflicts)
                        VALUES(:required, :name, :channel, :extension,
                               :exclude, :conflicts)';

                    $stmt1 = static::$databases[$this->_path]->prepare($sql);
                    foreach ($d->exclude as $exclude) {
                        $stmt1->clear();
                        $req = ($required == 'required' ? 1 : 0);
                        $stmt1->bindParam(':required', $req, SQLITE3_INTEGER);
                        $stmt1->bindParam(':name', $n);
                        $stmt1->bindParam(':channel', $c);
                        $stmt1->bindParam(':extension', $ext);
                        $stmt1->bindParam(':exclude', $exclude);
                        $con = $d->conflicts;
                        $stmt1->bindParam(':conflicts', $con, SQLITE3_INTEGER);

                        if (!@$stmt1->execute()) {
                            static::$databases[$this->_path]->exec('ROLLBACK');
                            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                $info->channel . '/' . $info->name . ' could not be installed in registry');
                        }
                    }
                    $stmt1->close();
                }
            }
        }
        $stmt->close();

        $sql = '
            INSERT INTO package_dependencies
                (required, packages_name, packages_channel, deppackage,
                 depchannel, conflicts, min, max, recommended, is_subpackage, providesextension)
            VALUES
                (:required, :name, :channel, :dep_package, :dep_channel,
                 :conflicts, :min, :max, :recommended, :sub, :ext)';
        $stmt = static::$databases[$this->_path]->prepare($sql);

        $first = true;
        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($info->dependencies[$required]->$package as $d) {
                    // $d is a PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object
                    $dchannel     = $d->channel;
                    $dmin         = $d->min;
                    $dmax         = $d->max;
                    $drecommended = $d->recommended;
                    $dname        = $d->name;
                    $sub          = $package == 'subpackage';
                    $ext          = $d->providesextension;

                    if (!$first) {
                        $stmt->clear();
                        $first = false;
                    }
                    $req = ($required == 'required' ? 1 : 0);
                    $stmt->bindParam(':required', $req, SQLITE3_INTEGER);
                    $stmt->bindParam(':name', $n);
                    $stmt->bindParam(':channel', $c);
                    $stmt->bindParam(':dep_package', $dname);
                    $stmt->bindParam(':dep_channel', $dchannel);
                    $con = $d->conflicts;
                    $stmt->bindParam(':conflicts', $con, SQLITE3_INTEGER);
                    $stmt->bindParam(':min', $dmin);
                    $stmt->bindParam(':max', $dmax);
                    $stmt->bindParam(':recommended', $drecommended);
                    $stmt->bindParam(':sub', $sub);
                    if ($ext) {
                        $stmt->bindParam(':ext', $ext);
                    } else {
                        $stmt->bindParam(':ext', $ext, SQLITE3_NULL);
                    }

                    if (!@$stmt->execute()) {
                        static::$databases[$this->_path]->exec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->name . ' could not be installed in registry');
                    }

                    if (isset($d->exclude)) {

                        $sql = '
                            INSERT INTO package_dependencies_exclude
                             (required, packages_name, packages_channel,
                              deppackage, depchannel, exclude, conflicts, is_subpackage)
                            VALUES(:required, :name, :channel, :dep_package,
                                :dep_channel, :exclude, :conflicts, :sub)';

                        $stmt1 = static::$databases[$this->_path]->prepare($sql);
                        foreach ($d->exclude as $exclude) {
                            $stmt1->clear();
                            $req = ($required == 'required' ? 1 : 0);
                            $stmt1->bindParam(':required', $req, SQLITE3_INTEGER);
                            $stmt1->bindParam(':name', $n);
                            $stmt1->bindParam(':channel', $c);
                            $stmt1->bindParam(':dep_package', $dname);
                            $stmt1->bindParam(':dep_channel', $dchannel);
                            $stmt1->bindParam(':exclude', $exclude);
                            $stmt1->bindParam(':sub', $sub);
                            $con = $d->conflicts;
                            $stmt1->bindParam(':conflicts', $con, SQLITE3_INTEGER);

                            if (!@$stmt1->execute()) {
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

        $sql = '
            INSERT INTO php_dependencies
              (packages_name, packages_channel, min, max)
            VALUES
                (:name, :channel, :min, :max)';

        $min = $info->dependencies['required']->php->min;
        $max = $info->dependencies['required']->php->max;
        $stmt = static::$databases[$this->_path]->prepare($sql);

        $stmt->bindParam(':name', $n);
        $stmt->bindParam(':channel', $c);
        $stmt->bindParam(':min', $min);
        if ($max === null) {
            $stmt->bindParam(':max', $max, SQLITE3_NULL);
        } else {
            $stmt->bindParam(':max', $max);
        }
        if (!@$stmt->execute()) {
            static::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry');
        }
        $stmt->close();

        $sql = '
            INSERT INTO php_dependencies_exclude
              (packages_name, packages_channel, exclude)
            VALUES
                (:name, :channel, :exclude)';
        $stmt = static::$databases[$this->_path]->prepare($sql);

        if ($info->dependencies['required']->php->exclude) {
            foreach ($info->dependencies['required']->php->exclude as $exclude) {
                $stmt->bindParam(':name', $n);
                $stmt->bindParam(':channel', $c);
                $stmt->bindParam(':exclude', $exclude);
                if (!$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
        }
        $stmt->close();

        $sql = '
            INSERT INTO pearinstaller_dependencies
              (packages_name, packages_channel, min, max)
            VALUES
                (:name, :channel, :min, :max)';

        $min = $info->dependencies['required']->pearinstaller->min;
        $max = $info->dependencies['required']->pearinstaller->max;
        $stmt = static::$databases[$this->_path]->prepare($sql);

        $stmt->bindParam(':name', $n);
        $stmt->bindParam(':channel', $c);
        $stmt->bindParam(':min', $min);
        if ($max === null) {
            $stmt->bindParam(':max', $max, SQLITE3_NULL);
        } else {
            $stmt->bindParam(':max', $max);
        }
        if (!@$stmt->execute()) {
            static::$databases[$this->_path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry');
        }
        $stmt->close();

        $sql = '
            INSERT INTO pearinstaller_dependencies_exclude
              (packages_name, packages_channel, exclude)
            VALUES
                (:name, :channel, :exclude)';
        $stmt = static::$databases[$this->_path]->prepare($sql);

        if ($info->dependencies['required']->pearinstaller->exclude) {
            foreach ($info->dependencies['required']->pearinstaller->exclude as $exclude) {
                $stmt->bindParam(':name', $n);
                $stmt->bindParam(':channel', $c);
                $stmt->bindParam(':exclude', $exclude);
                if (!$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
        }
        $stmt->close();

        if (isset($info->dependencies['required']->os)) {
            $sql = '
                INSERT INTO os_dependencies
                  (packages_name, packages_channel, osname, conflicts)
                VALUES
                    (:name, :channel, :os, :conflicts)';
            $stmt = static::$databases[$this->_path]->prepare($sql);

            foreach ($info->dependencies['required']->os as $dep) {

                $stmt->clear();
                $stmt->bindParam(':name', $n);
                $stmt->bindParam(':channel', $c);
                $name = $dep->name;
                $stmt->bindParam(':os', $name);
                $conflicts = $dep->conflicts;
                $stmt->bindParam(':conflicts', $conflicts, SQLITE3_INTEGER);
                if (!@$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
            $stmt->close();
        }

        if (isset($info->dependencies['required']->arch)) {
            $sql = '
                INSERT INTO arch_dependencies
                  (packages_name, packages_channel, pattern, conflicts)
                VALUES
                    (:name, :channel, :arch, :conflicts)';

            $stmt = static::$databases[$this->_path]->prepare($sql);
            foreach ($info->dependencies['required']->arch as $dep) {

                $stmt->clear();
                $stmt->bindParam(':name', $n);
                $stmt->bindParam(':channel', $c);
                $name = $dep->pattern;
                $stmt->bindParam(':arch', $name);
                $conflicts = isset($dep->conflicts);
                $stmt->bindParam(':conflicts', $conflicts, SQLITE3_INTEGER);
                if (!@$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }
            }
            $stmt->close();
        }

        foreach ($info->dependencies['group'] as $group) {
            $gn = $group->name;
            $gh = $group->hint;

            $sql = '
                INSERT INTO dep_groups
                    (packages_name, packages_channel, groupname, grouphint)
                VALUES
                    (:name, :channel, :groupname, :grouphint)';
            $stmt = static::$databases[$this->_path]->prepare($sql);
            $stmt->bindParam(':name', $n);
            $stmt->bindParam(':channel', $c);
            $stmt->bindParam(':groupname', $gn);
            $stmt->bindParam(':grouphint', $gh);

            if (!@$stmt->execute()) {
                static::$databases[$this->_path]->exec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->channel . '/' . $info->name . ' could not be installed in registry');
            }
            $stmt->close();

            $sql = '
                INSERT INTO extension_dependencies
                    (required, packages_name, packages_channel, extension,
                     conflicts, min, max, recommended, groupname)
                VALUES
                    (0, :name, :channel, :extension,
                     :conflicts, :min, :max, :recommended, :groupname)';

            $stmt = static::$databases[$this->_path]->prepare($sql);
            foreach ($group->extension as $d) {
                // $d is a PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object
                $dmin         = $d->min;
                $dmax         = $d->max;
                $drecommended = $d->recommended;
                $ext          = $d->name;

                $stmt->clear();
                $stmt->bindParam(':name', $n);
                $stmt->bindParam(':channel', $c);
                $stmt->bindParam(':extension', $ext);
                $con = $d->conflicts;
                $stmt->bindParam(':conflicts', $con, SQLITE3_INTEGER);
                $stmt->bindParam(':min', $dmin);
                $stmt->bindParam(':max', $dmax);
                $stmt->bindParam(':recommended', $drecommended);
                $stmt->bindParam(':groupname', $gn);

                if (!@$stmt->execute()) {
                    static::$databases[$this->_path]->exec('ROLLBACK');
                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                        $info->channel . '/' . $info->name . ' could not be installed in registry');
                }

                if (isset($d->exclude)) {
                    $sql = '
                        INSERT INTO extension_dependencies_exclude
                         (required, packages_name, packages_channel,
                          extension, exclude, conflicts, groupname)
                        VALUES(0, :name, :channel, :extension,
                               :exclude, :conflicts, :groupname)';

                    $stmt1 = static::$databases[$this->_path]->prepare($sql);
                    foreach ($d->exclude as $exclude) {
                        $stmt1->clear();
                        $stmt1->bindParam(':name', $n);
                        $stmt1->bindParam(':channel', $c);
                        $stmt1->bindParam(':extension', $ext);
                        $stmt1->bindParam(':exclude', $exclude);
                        $con = $d->conflicts;
                        $stmt1->bindParam(':conflicts', $con, SQLITE3_INTEGER);
                        $stmt1->bindParam(':groupname', $gn);

                        if (!@$stmt1->execute()) {
                            static::$databases[$this->_path]->exec('ROLLBACK');
                            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                $info->channel . '/' . $info->name . ' could not be installed in registry');
                        }
                    }
                    $stmt1->close();
                }
            }
            $stmt->close();
    
            $sql = '
                INSERT INTO package_dependencies
                  (required, packages_name, packages_channel, deppackage,
                   depchannel, conflicts, min, max, recommended, is_subpackage, groupname, providesextension)
                VALUES
                    (0, :name, :channel, :dep_package, :dep_channel, :conflicts, :min, :max, :recommended, :sub,
                     :group, :ext)';

            $stmt = static::$databases[$this->_path]->prepare($sql);
            foreach (array('package', 'subpackage') as $package) {
                foreach ($group->$package as $d) {
                    // $d is a PEAR2_Pyrus_PackageFile_v2_Dependencies_Package object
                    $dchannel     = $d->channel;
                    $dmin         = $d->min;
                    $dmax         = $d->max;
                    $dname        = $d->name;
                    $drecommended = $d->recommended;
                    $sub          = $package == 'subpackage';
                    $ext          = $d->providesextension;

                    $stmt->clear();
                    $stmt->bindParam(':name', $n);
                    $stmt->bindParam(':channel', $c);
                    $stmt->bindParam(':dep_package', $dname);
                    $stmt->bindParam(':dep_channel', $dchannel);
                    $con = $d->conflicts;
                    $stmt->bindParam(':conflicts', $con, SQLITE3_INTEGER);
                    $stmt->bindParam(':min', $dmin);
                    $stmt->bindParam(':max', $dmax);
                    $stmt->bindParam(':recommended', $drecommended);
                    $stmt->bindParam(':sub', $sub);
                    $stmt->bindParam(':group', $gn);
                    if ($ext) {
                        $stmt->bindParam(':ext', $ext);
                    } else {
                        $stmt->bindParam(':ext', $ext, SQLITE3_NULL);
                    }

                    if (!@$stmt->execute()) {
                        static::$databases[$this->_path]->exec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->name . ' could not be installed in registry');
                    }

                    if (isset($d->exclude)) {

                        $sql = '
                            INSERT INTO package_dependencies_exclude
                             (required, packages_name, packages_channel,
                              deppackage, depchannel, exclude, conflicts, is_subpackage, groupname)
                            VALUES(0, :name, :channel, :dep_package,
                                :dep_channel, :exclude, :conflicts, :sub, :group)';

                        $stmt1 = static::$databases[$this->_path]->prepare($sql);
                        foreach ($d->exclude as $exclude) {
                            $stmt1->clear();
                            $req = 0;
                            $stmt1->bindParam(':required', $req, SQLITE3_INTEGER);
                            $stmt1->bindParam(':name',        $n);
                            $stmt1->bindParam(':channel',     $c);
                            $stmt1->bindParam(':dep_package', $dname);
                            $stmt1->bindParam(':dep_channel', $dchannel);
                            $stmt1->bindParam(':exclude',     $exclude);
                            $stmt1->bindParam(':sub', $sub);
                            $stmt1->bindParam(':group', $gn);
                            $con = $d->conflicts;
                            $stmt1->bindParam(':conflicts', $con, SQLITE3_INTEGER);

                            if (!@$stmt1->execute()) {
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

        $channel = PEAR2_Pyrus_Config::current()->channelregistry[$channel]->name;
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
        $result = @$stmt->execute();

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

        if ($field == 'api-state') {
            $field = 'apistability';
        } elseif ($field == 'state') {
            $field = 'stability';
        } elseif ($field == 'release-version') {
            $field = 'version';
        } elseif ($field == 'api-version') {
            $field = 'apiversion';
        } elseif ($field == 'notes') {
            $field = 'releasenotes';
        } elseif ($field == 'date') {
            $field = 'releasedate';
        } elseif ($field == 'time') {
            $field = 'releasetime';
        } elseif ($field == 'installedfiles') {
            $ret = array();
            $sql = 'SELECT
                        configpath, relativepath, role, origpath, baseinstalldir
                    FROM files
                    WHERE
                        packages_name = :name AND packages_channel = :channel';

            $stmt = static::$databases[$this->_path]->prepare($sql);
            $stmt->bindParam(':name',    $package);
            $stmt->bindParam(':channel', $channel);
            $result = @$stmt->execute();

            if (!$result) {
                $error = static::$databases[$this->_path]->lastErrorMsg();
                throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                    ': ' . $error);
            }

            while ($file = $result->fetchArray(SQLITE3_ASSOC)) {
                if ($file['baseinstalldir']) {
                    $ret[$file['configpath'] . DIRECTORY_SEPARATOR . $file['relativepath']] =
                                                      array('role' => $file['role'],
                                                       'name' => $file['origpath'],
                                                       'baseinstalldir' => $file['baseinstalldir'],
                                                       'installed_as' => $file['configpath'] . DIRECTORY_SEPARATOR . $file['relativepath'],
                                                       'relativepath' => $file['relativepath'],
                                                       'configpath' => $file['configpath'],
                                                      );
                } else {
                    $ret[$file['configpath'] . DIRECTORY_SEPARATOR . $file['relativepath']] =
                                                      array('role' => $file['role'],
                                                       'name' => $file['origpath'],
                                                       'installed_as' => $file['configpath'] . DIRECTORY_SEPARATOR . $file['relativepath'],
                                                       'relativepath' => $file['relativepath'],
                                                       'configpath' => $file['configpath'],
                                                      );
                }
            }
            $stmt->close();

            return $ret;
        } elseif ($field == 'dirtree') {
            // if we are :memory: this can't work
            if ($this->_path === ':memory:') {
                return array();
            }

            $actual = dirname($this->_path);

            $files = $this->info($package, $channel, 'installedfiles');
            foreach ($files as $file => $unused) {
                do {
                    $file = dirname($file);
                    if (strlen($file) > strlen($actual)) {
                        $ret[$file] = 1;
                    }
                } while (strlen($file) > strlen($actual));
            }
            $ret = array_keys($ret);
            usort($ret, 'strnatcasecmp');
            return array_reverse($ret);
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
        $result = @$stmt->execute();

        while ($res = $result->fetchArray(SQLITE3_NUM)) {
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
        $result = @$stmt->execute();

        if (!$result) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $package . ', no maintainers registered');
        }

        while ($maintainer = $result->fetchArray(SQLITE3_ASSOC)) {
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

        $ret->version['release']   = $this->info($package, $channel, 'version');
        $ret->version['api']       = $this->info($package, $channel, 'apiversion');
        $ret->stability['release'] = $this->info($package, $channel, 'stability');
        $ret->stability['api']     = $this->info($package, $channel, 'apistability');
        $uri     = $this->info($package, $channel, 'licenseuri');
        $path    = $this->info($package, $channel, 'licensepath');
        $license = $this->info($package, $channel, 'license');
        if ($uri) {
            $ret->rawlicense = array('attribs' => array('uri' => $uri), '_content' => $license);
        } elseif ($path) {
            $ret->rawlicense = array('attribs' => array('path' => $path), '_content' => $license);
        } else {
            $ret->license = $license;
        }
        $ret->notes = $this->info($package, $channel, 'releasenotes');

        $sql = 'SELECT * FROM files
                WHERE packages_name = :name AND packages_channel = :channel';

        $stmt = static::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':name',    $package);
        $stmt->bindParam(':channel', $channel);
        $result = @$stmt->execute();

        if (!$result) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $package . ', no files registered');
        }

        while ($file = $result->fetchArray(SQLITE3_ASSOC)) {
            $ret->files[$file['origpath']] = array_merge(
                                                array('attribs' => array('role' => $file['role'])),
                                                unserialize($file['tasks']));
            if ($file['baseinstalldir']) {
                $ret->setFileAttribute($file['origpath'], 'baseinstalldir', $file['baseinstalldir']);
            }
        }
        $stmt->close();

        $sql = 'SELECT dirname, baseinstall FROM baseinstalldirs
                WHERE packages_name = :name AND packages_channel = :channel';

        $stmt = static::$databases[$this->_path]->prepare($sql);
        $stmt->bindParam(':name',    $package);
        $stmt->bindParam(':channel', $channel);
        $result = @$stmt->execute();

        if (!$result) {
            throw new PEAR2_Pyrus_Registry_Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $package . ', no files registered');
        }

        $dirs = array();
        while ($dir = $result->fetchArray(SQLITE3_ASSOC)) {
            $dirs[$dir['dirname']] = $dir['baseinstall'];
        }
        $ret->setBaseInstallDirs($dirs);
        $stmt->close();

        $sql = 'SELECT * FROM configureoptions
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($option = $a->fetchArray()) {
            $ret->configureoption[$option['name']]->prompt($option['prompt'])->default($option['defaultValue']);
        }
        $this->fetchCompatible($ret);
        $this->fetchDeps($ret);
        $ret->release = null;
        return $ret;
    }

    function fetchCompatible(PEAR2_Pyrus_IPackageFile $ret)
    {
        $package = $ret->name;
        $channel = $ret->channel;
        $sql = 'SELECT * FROM compatible_releases
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray(SQLITE3_ASSOC)) {
            $ret->compatible[$dep['compat_channel'] . '/' . $dep['compat_package']]->min =
                $dep['min'];
            $ret->compatible[$dep['compat_channel'] . '/' . $dep['compat_package']]->max =
                $dep['max'];
        }

        $sql = 'SELECT * FROM compatible_releases_exclude
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray(SQLITE3_ASSOC)) {
            $ret->compatible[$dep['compat_channel'] . '/' . $dep['compat_package']]->exclude =
                $dep['exclude'];
        }
    }

    function fetchDeps(PEAR2_Pyrus_IPackageFile $ret)
    {
        $package = $ret->name;
        $channel = $ret->channel;
        $sql = 'SELECT * FROM php_dependencies
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->php->min = $dep['min'];
            $ret->dependencies['required']->php->max = $dep['max'];
        }

        $sql = 'SELECT * FROM php_dependencies_exclude
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->php->exclude($dep['exclude']);
        }

        $sql = 'SELECT * FROM pearinstaller_dependencies
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->pearinstaller->min = $dep['min'];
            $ret->dependencies['required']->pearinstaller->max = $dep['max'];
        }

        $sql = 'SELECT * FROM pearinstaller_dependencies_exclude
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->pearinstaller->exclude($dep['exclude']);
        }

        $sql = 'SELECT * FROM os_dependencies
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray(SQLITE3_ASSOC)) {
            $ret->dependencies['required']->os[$dep['osname']] = !$dep['conflicts'];
            $rawdeps = $ret->rawdeps;
        }

        $sql = 'SELECT * FROM arch_dependencies
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $a = static::$databases[$this->_path]->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->arch[$dep['pattern']] = !$dep['conflicts'];
        }

        $sql = 'SELECT * FROM package_dependencies
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
                //ORDER BY required, deppackage, depchannel, conflicts';
        $package_deps = static::$databases[$this->_path]->query($sql);

        $sql = 'SELECT * FROM package_dependencies_exclude
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
                //ORDER BY required, deppackage, depchannel, conflicts, exclude';
        $excludes = static::$databases[$this->_path]->query($sql);
        if (!$package_deps) {
            $ret = $this->fetchDepGroups($ret);
            return $ret;
        }

        $provides = array();
        while ($dep = $package_deps->fetchArray(SQLITE3_ASSOC)) {
            $required = $dep['required'] ? 'required' : 'optional';
            $package = $dep['is_subpackage'] ? 'subpackage' : 'package';
            if ($dep['groupname']) {
                $group = $dep['groupname'];
                $d = $ret->dependencies['group']->$group->{$package}[$dep['depchannel'] . '/' . $dep['deppackage']];
            } else {
                $d = $ret->dependencies[$required]->{$package}[$dep['depchannel'] . '/' . $dep['deppackage']];
            }
            $d->min($dep['min']);
            $d->max($dep['max']);
            if ($dep['conflicts']) {
                $d->conflicts();
            }
            $d->recommended($dep['recommended']);
            $provides[] = $dep;
        }

        while ($dep = $excludes->fetchArray(SQLITE3_ASSOC)) {
            $required = $dep['required'] ? 'required' : 'optional';
            $package = $dep['is_subpackage'] ? 'subpackage' : 'package';

            if ($dep['groupname']) {
                $group = $dep['groupname'];
                $d = $ret->dependencies['group']->$group->{$package}[$dep['depchannel'] . '/' . $dep['deppackage']];
            } else {
                $d = $ret->dependencies[$required]->{$package}[$dep['depchannel'] . '/' . $dep['deppackage']];
            }

            $d->exclude($dep['exclude']);
        }
        foreach ($provides as $dep){
            $required = $dep['required'] ? 'required' : 'optional';
            $package = $dep['is_subpackage'] ? 'subpackage' : 'package';
            if ($dep['groupname']) {
                $group = $dep['groupname'];
                $ret->dependencies['group']->$group->{$package}[$dep['depchannel'] . '/' . $dep['deppackage']]->providesextension($dep['providesextension']);
            } else {
                $ret->dependencies[$required]->{$package}[$dep['depchannel'] . '/' . $dep['deppackage']]->providesextension($dep['providesextension']);
            }
        }

        $ret = $this->fetchExtensionDeps($ret);
        $ret = $this->fetchDepGroups($ret);
        return $ret;
    }

    function fetchDepGroups(PEAR2_Pyrus_IPackageFile $ret)
    {
        $package = $ret->name;
        $channel = $ret->channel;

        $sql = 'SELECT * FROM dep_groups
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $groups = static::$databases[$this->_path]->query($sql);
        if ($groups) {
            while ($group = $groups->fetchArray(SQLITE3_ASSOC)) {
                $ret->dependencies['group']->{$group['groupname']}->hint = $group['grouphint'];
            }
        }
        return $ret;
    }

    function fetchExtensionDeps(PEAR2_Pyrus_IPackageFile $ret)
    {
        $package = $ret->name;
        $channel = $ret->channel;
        $sql = 'SELECT * FROM extension_dependencies
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $extension_deps = static::$databases[$this->_path]->query($sql);

        $sql = 'SELECT * FROM extension_dependencies_exclude
                WHERE
                    packages_name = "' . static::$databases[$this->_path]->escapeString($package) . '" AND
                    packages_channel = "' . static::$databases[$this->_path]->escapeString($channel) . '"';
        $excludes = static::$databases[$this->_path]->query($sql);
        if (!$extension_deps) {
            return $ret;
        }
        while ($dep = $extension_deps->fetchArray(SQLITE3_ASSOC)) {
            $required = $dep['required'] ? 'required' : 'optional';
            if ($dep['groupname']) {
                $group = $dep['groupname'];
                $d = $ret->dependencies['group']->$group->extension[$dep['extension']];
            } else {
                $d = $ret->dependencies[$required]->extension[$dep['extension']];
            }
            $d->min($dep['min']);
            $d->max($dep['max']);
            if ($dep['conflicts']) {
                $d->conflicts();
            }
            $d->recommended($dep['recommended']);
            $provides[] = $dep;
        }

        while ($dep = $excludes->fetchArray(SQLITE3_ASSOC)) {
            $required = $dep['required'] ? 'required' : 'optional';

            if ($dep['groupname']) {
                $group = $dep['groupname'];
                $d = $ret->dependencies['group']->$group->extension[$dep['extension']];
            } else {
                $d = $ret->dependencies[$required]->extension[$dep['extension']];
            }

            $d->exclude($dep['exclude']);
        }

        return $ret;
    }

    public function getDependentPackages(PEAR2_Pyrus_IPackageFile $package)
    {
        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT
                    packages_channel, packages_name
                FROM package_dependencies
                WHERE
                    deppackage = :name AND depchannel = :channel
                ORDER BY packages_channel, packages_name';
        $stmt = static::$databases[$this->_path]->prepare($sql);
        $pn = $package->name;
        $stmt->bindParam(':name', $pn, SQLITE3_TEXT);
        $pp = $package->channel;
        $stmt->bindParam(':channel', $pp, SQLITE3_TEXT);
        $result = @$stmt->execute();

        while ($res = $result->fetchArray()) {
            try {
                $ret[] = $this->package[$res[0] . '/' . $res[1]];
            } catch (Exception $e) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Could not retrieve ' .
                    'dependent package ' . $res[0] . '/' . $res[1], $e);
            }
        }

        return $ret;
    }

    /**
     * Detect any files already installed that would be overwritten by
     * files inside the package represented by $package
     */
    public function detectFileConflicts(PEAR2_Pyrus_IPackageFile $package)
    {
        if (!isset(static::$databases[$this->_path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }

        $ret = array();
        $sql = 'SELECT
                    packages_channel, packages_name
                FROM files
                WHERE
                    packagepath = :path
                ORDER BY packages_channel, packages_name';
        $stmt = static::$databases[$this->_path]->prepare($sql);
        // now iterate over each file in the package, and note all the conflicts
        $roles = array();
        foreach (PEAR2_Pyrus_Installer_Role::getValidRoles($package->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                PEAR2_Pyrus_Installer_Role::factory($package->getPackageType(), $role);
        }
        $ret = array();
        $config = PEAR2_Pyrus_Config::current();
        foreach ($package->installcontents as $file) {
            $stmt->reset();
            $relativepath = $roles[$file->role]->getRelativeLocation($package, $file);
            if (!$relativepath) {
                continue;
            }
            $testpath = $config->{$roles[$file->role]->getLocationConfig()} .
                    DIRECTORY_SEPARATOR . $relativepath;
            $stmt->bindParam(':path', $testpath, SQLITE3_TEXT);
            $result = $stmt->execute();

            while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
                $ret[] = array($relativepath => $res['packages_channel'] . '/' . $res['packages_name']);
            }
        }
        return $ret;
    }

    /**
     * Returns a list of registries present in the PEAR installation at $path
     * @param string
     * @return array
     */
    static public function detectRegistries($path)
    {
        if (isset(PEAR2_Pyrus::$options['packagingroot'])) {
            $path = PEAR2_Pyrus::prepend(PEAR2_Pyrus::$options['packagingroot'], $path);
        }
        if (file_exists($path . '/.pear2registry') || is_file($path . '/.pear2registry')) {
            return array('Sqlite3');
        }
        return array();
    }

    /**
     * Completely remove all traces of an sqlite3 registry
     */
    static public function removeRegistry($path)
    {
        if ($path === ':memory:') {
            unset(static::$databases[$path]);
            return;
        }
        if (dirname($path) . DIRECTORY_SEPARATOR . '.pear2registry' != $path) {
            $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
        }
        if (!file_exists($path)) {
            return;
        }
        if (isset(static::$databases[$path])) {
            static::$databases[$path]->close();
            unset(static::$databases[$path]);
        }
        if (!@unlink($path)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot remove Sqlite3 registry: Unable to remove SQLite database');
        }
    }
}