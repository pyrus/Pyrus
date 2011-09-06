<?php
/**
 * \Pyrus\Registry\Sqlite3
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
 * This is the central registry, that is used for all installer options,
 * stored as an SQLite3 database
 *
 * Registry information that must be stored:
 *
 * - A list of installed packages
 * - the files in each package
 * - known channels
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @author    Helgi Þormar Þorbjörnsson <helgi@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Registry;
use \Pyrus\Main as Main,
    \Pyrus\Config as Config,
    \Pyrus\Installer\Role as Role;
class Sqlite3 extends \Pyrus\Registry\Base
{
    /**
     * The database resources, stored by path
     *
     * This allows singleton access to the database by separate objects
     * @var SQLite3
     */
    static protected $databases = array();
    private $_path;
    private $_initialized = false;
    protected $readonly;

    /**
     * Initialize the registry
     *
     * @param unknown_type $path
     */
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        $this->_path = static::initRegistry($path, $readonly);
        $this->_initialized = true;
    }

    private function _prepareWrite()
    {
        if (static::existsRegistry($this->_path)) {
            return;
        }
        static::initRegistry($this->_path, $this->readonly);

        if (!static::existsRegistry($this->_path)) {
            throw new Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }
    }

    function getDatabase()
    {
        return $this->_path;
    }

    /**
     * Add an installed package to the registry
     *
     * @param \Pyrus\PackageFileInterface $info
     */
    function install(\Pyrus\PackageFileInterface $info, $replace = false)
    {
        if ($this->readonly) {
            throw new Exception('Cannot install package, registry is read-only');
        }
        $this->_prepareWrite();
        $database = static::getRegistry($this->_path);

        try {
            $this->uninstall($info->name, $info->channel);
        } catch (\Exception $e) {
            // ignore errors
        }

        $database->enableExceptions(true);
        try {
            $this->_install($info, $replace);
        } catch (\Exception $e) {
            $database->enableExceptions(false);
            throw new Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry: ' .
                $e->getMessage(), $e);
        }
        $database->enableExceptions(false);
    }

    function _install($info, $replace)
    {
        $database = static::getRegistry($this->_path);

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

        $sql = '
            INSERT INTO packages
              (lcname, name, channel, version, apiversion, summary, packagetype,
               description, stability, apistability, releasedate,
               releasetime, license, licenseuri, licensepath,
               releasenotes, lastinstalledversion, installedwithpear,
               installtimeconfig)
            VALUES(:lcname, :name, :channel, :versionrelease, :versionapi, :summary,
                :packagetype, :description, :stabilityrelease, :stabilityapi,
                :date, :time, :license, :licenseuri, :licensepath, :notes,
                :lastinstalledv, :lastinstalledp, :lastinstalltime
            )';

        $stmt = $database->prepare($sql);
        $n = $info->name;
        $c = $info->channel;
        $stmt->bindValue(':lcname',              strtolower($n));
        $stmt->bindValue(':name',              $n);
        $stmt->bindValue(':channel',           $c);
        $stmt->bindValue(':versionrelease',    $info->version['release']);
        $stmt->bindValue(':versionapi',        $info->version['api']);
        $stmt->bindValue(':summary',           $info->summary);
        $stmt->bindValue(':description',       $info->description);
        $stmt->bindValue(':packagetype',       $info->type);
        $stmt->bindValue(':stabilityrelease',  $info->stability['release']);
        $stmt->bindValue(':stabilityapi',      $info->stability['api']);
        $stmt->bindValue(':date',              $info->date);
        $stmt->bindValue(':time',              $info->time);
        $stmt->bindValue(':license',           $info->license['name']);
        $stmt->bindValue(':licenseuri',        $licuri, ($licuri === null) ? SQLITE3_NULL : SQLITE3_TEXT);
        $stmt->bindValue(':licensepath',       $licpath, ($licpath === null) ? SQLITE3_NULL : SQLITE3_TEXT);
        $stmt->bindValue(':notes',             $info->notes);
        $stmt->bindValue(':lastinstalledv',    null, SQLITE3_NULL);
        if ('@PACKAGE_VERSION@' == '@'.'PACKAGE_VERSION@') {
            $v = '2.0.0a1';
        } else {
            $v = '@PACKAGE_VERSION@';
        }
        $stmt->bindValue(':lastinstalledp',    $v);
        $stmt->bindValue(':lastinstalltime',   Config::configSnapshot());

        $stmt->execute();
        $stmt->close();
        $n = strtolower($n);

        $sql = '
            INSERT INTO maintainers
              (packages_name, packages_channel, role, name, user, email, active)
            VALUES
                (:name, :channel, :role, :m_name, :m_user, :m_email, :m_active)';

        $stmt = $database->prepare($sql);
        foreach ($info->allmaintainers as $role => $maintainers) {
            foreach ($maintainers as $maintainer) {
                $stmt->clear();
                $stmt->bindValue(':name',     $n);
                $stmt->bindValue(':channel',  $c);
                $stmt->bindValue(':role',     $role);
                $stmt->bindValue(':m_name',   $maintainer->name);
                $stmt->bindValue(':m_user',   $maintainer->user);
                $stmt->bindValue(':m_email',  $maintainer->email);
                $stmt->bindValue(':m_active', $maintainer->active);

                @$stmt->execute();
            }
        }
        $stmt->close();

        $curconfig = Config::current();
        $roles     = array();

        $sql = '
            INSERT INTO configureoptions
              (packages_name, packages_channel, name, prompt, defaultValue)
            VALUES(:name, :channel, :oname, :prompt, :default)';

        \Pyrus\Logger::log(4, 'Preparing SQL: ' . $sql);
        $stmt = $database->prepare($sql);

        $stmt->bindValue(':name',     $n);
        \Pyrus\Logger::log(4, 'Binding SQL parameter :name: ' . var_export($n, true));
        $stmt->bindValue(':channel',  $c);
        \Pyrus\Logger::log(4, 'Binding SQL parameter :channel: ' . var_export($c, true));

        foreach ($info->installrelease->configureoption as $option) {
            $stmt->bindValue(':oname', $option->name);
            \Pyrus\Logger::log(4, 'Binding SQL parameter :oname: ' . var_export($option->name, true));
            $stmt->bindValue(':prompt', $option->prompt);
            \Pyrus\Logger::log(4, 'Binding SQL parameter :prompt: ' . var_export($option->prompt, true));
            if ($option->default === null) {
                $stmt->bindValue(':default', null, SQLITE3_NULL);
                \Pyrus\Logger::log(4, 'Binding SQL parameter :default: NULL');
            } else {
                $stmt->bindValue(':default', $option->default);
                \Pyrus\Logger::log(4, 'Binding SQL parameter :default: ' . var_export($option->default, true));
            }
            $stmt->execute();
        }

        $sql = '
            INSERT INTO files
              (packages_name, packages_channel, packagepath, configpath, role,
               relativepath, origpath, baseinstalldir, tasks, md5sum)
            VALUES(:name, :channel, :path, :configpath, :role, :relativepath, :origpath, :baseinstall, :tasks, :md5)';

        $stmt = $database->prepare($sql);

        $stmt->bindValue(':name',     $n);
        $stmt->bindValue(':channel',  $c);
        foreach (Role::getValidRoles($info->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] =
                Role::factory($info->getPackageType(), $role);
        }

        foreach ($info->installcontents as $file) {
            $relativepath = $roles[$file->role]->getRelativeLocation($info, $file);
            if (!$relativepath) {
                continue;
            }

            $p = $curconfig->{$roles[$file->role]->getLocationConfig()};
            $stmt->bindValue(':relativepath', $relativepath);
            $stmt->bindValue(':configpath',  $p);
            $stmt->bindValue(':path', $p . DIRECTORY_SEPARATOR . $relativepath);
            $stmt->bindValue(':origpath',     $file->packagedname);
            $stmt->bindValue(':role',         $file->role);
            $stmt->bindValue(':baseinstall',  $file->baseinstalldir);
            $stmt->bindValue(':tasks',        serialize($file->tasks));
            if ($file->md5sum) {
                $stmt->bindValue(':md5', $file->md5sum);
            } else {
                // clearly the person installing doesn't care about this, so
                // use a dummy value
                $stmt->bindValue(':md5', md5(''));
            }

            $stmt->execute();
        }
        $stmt->close();

        $sql = '
            INSERT INTO baseinstalldirs
              (packages_name, packages_channel, dirname, baseinstall)
            VALUES(:name, :channel, :dirname, :baseinstall)';

        $stmt = $database->prepare($sql);

        foreach ($info->getBaseInstallDirs() as $dir => $base) {
            $stmt->bindValue(':name',        $n);
            $stmt->bindValue(':channel',     $c);
            $stmt->bindValue(':dirname',     $dir);
            $stmt->bindValue(':baseinstall', $base);

            $stmt->execute();
        }
        $stmt->close();

        if (count($info->compatible)) {
            $sql = '
                INSERT INTO compatible_releases
                    (packages_name, packages_channel,
                     compat_package, compat_channel, min, max)
                VALUES
                    (:name, :channel, :cname, :cchannel, :min, :max)';
            $stmt = $database->prepare($sql);

            $stmt->bindValue(':name', $n);
            $stmt->bindValue(':channel', $c);

            $sql2 = '
                INSERT INTO compatible_releases_exclude
                    (packages_name, packages_channel,
                     compat_package, compat_channel, exclude)
                VALUES
                    (:name, :channel, :cname, :cchannel, :exclude)';
            $stmt2 = $database->prepare($sql2);

            $stmt2->bindValue(':name', $n);
            $stmt2->bindValue(':channel', $c);
            foreach ($info->compatible as $compatible) {
                $stmt->bindValue(':cname', $compatible->name);
                $stmt->bindValue(':cchannel', $compatible->channel);
                $stmt->bindValue(':min', $compatible->min);
                $stmt->bindValue(':max', $compatible->max);
                $stmt->execute();

                if (isset($compatible->exclude)) {
                    $stmt2->bindValue(':cname', $compatible->name);
                    $stmt2->bindValue(':cchannel', $compatible->channel);
                    foreach ($compatible->exclude as $exclude) {
                        $stmt2->bindValue(':exclude', $exclude);
                        $stmt2->execute();
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
        $stmt = $database->prepare($sql);
        foreach (array('required', 'optional') as $required) {
            foreach ($info->dependencies[$required]->extension as $d) {
                // $d is a \Pyrus\PackageFile\v2\Dependencies\Package object
                $req = ($required == 'required' ? 1 : 0);
                $stmt->bindValue(':required', $req, SQLITE3_INTEGER);
                $stmt->bindValue(':name', $n);
                $stmt->bindValue(':channel', $c);
                $stmt->bindValue(':extension', $d->name);
                $stmt->bindValue(':conflicts', $d->conflicts, SQLITE3_INTEGER);
                $stmt->bindValue(':min', $d->min);
                $stmt->bindValue(':max', $d->max);
                $stmt->bindValue(':recommended', $d->recommended);

                $stmt->execute();

                if (isset($d->exclude)) {
                    $sql = '
                        INSERT INTO extension_dependencies_exclude
                         (required, packages_name, packages_channel,
                          extension, exclude, conflicts)
                        VALUES(:required, :name, :channel, :extension,
                               :exclude, :conflicts)';

                    $stmt1 = $database->prepare($sql);
                    foreach ($d->exclude as $exclude) {
                        $stmt1->clear();
                        $req = ($required == 'required' ? 1 : 0);
                        $stmt1->bindValue(':required', $req, SQLITE3_INTEGER);
                        $stmt1->bindValue(':name', $n);
                        $stmt1->bindValue(':channel', $c);
                        $stmt1->bindValue(':extension', $d->name);
                        $stmt1->bindValue(':exclude', $exclude);
                        $stmt1->bindValue(':conflicts', $d->conflicts, SQLITE3_INTEGER);

                        $stmt1->execute();
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
        $stmt = $database->prepare($sql);

        $first = true;
        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($info->dependencies[$required]->$package as $d) {
                    // $d is a \Pyrus\PackageFile\v2\Dependencies\Package object
                    $sub          = $package == 'subpackage';

                    if (!$first) {
                        $stmt->clear();
                        $first = false;
                    }
                    $req = ($required == 'required' ? 1 : 0);
                    $stmt->bindValue(':required', $req, SQLITE3_INTEGER);
                    $stmt->bindValue(':name', $n);
                    $stmt->bindValue(':channel', $c);
                    $stmt->bindValue(':dep_package', $d->name);
                    $stmt->bindValue(':dep_channel', $d->channel);
                    $con = $d->conflicts;
                    $stmt->bindValue(':conflicts', $con, SQLITE3_INTEGER);
                    $stmt->bindValue(':min', $d->min);
                    $stmt->bindValue(':max', $d->max);
                    $stmt->bindValue(':recommended', $d->recommended);
                    $stmt->bindValue(':sub', $sub);
                    if ($d->providesextension) {
                        $stmt->bindValue(':ext', $d->providesextension);
                    } else {
                        $stmt->bindValue(':ext', null, SQLITE3_NULL);
                    }

                    $stmt->execute();

                    if (isset($d->exclude)) {

                        $sql = '
                            INSERT INTO package_dependencies_exclude
                             (required, packages_name, packages_channel,
                              deppackage, depchannel, exclude, conflicts, is_subpackage)
                            VALUES(:required, :name, :channel, :dep_package,
                                :dep_channel, :exclude, :conflicts, :sub)';

                        $stmt1 = $database->prepare($sql);
                        foreach ($d->exclude as $exclude) {
                            $stmt1->clear();
                            $req = ($required == 'required' ? 1 : 0);
                            $stmt1->bindValue(':required', $req, SQLITE3_INTEGER);
                            $stmt1->bindValue(':name', $n);
                            $stmt1->bindValue(':channel', $c);
                            $stmt1->bindValue(':dep_package', $d->name);
                            $stmt1->bindValue(':dep_channel', $d->channel);
                            $stmt1->bindValue(':exclude', $exclude);
                            $stmt1->bindValue(':sub', $sub);
                            $con = $d->conflicts;
                            $stmt1->bindValue(':conflicts', $d->conflicts, SQLITE3_INTEGER);

                            $stmt1->execute();
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

        $max = $info->dependencies['required']->php->max;
        $stmt = $database->prepare($sql);

        $stmt->bindValue(':name', $n);
        $stmt->bindValue(':channel', $c);
        $stmt->bindValue(':min', $info->dependencies['required']->php->min);
        if ($max === null) {
            $stmt->bindValue(':max', $max, SQLITE3_NULL);
        } else {
            $stmt->bindValue(':max', $max);
        }
        $stmt->execute();
        $stmt->close();

        $sql = '
            INSERT INTO php_dependencies_exclude
              (packages_name, packages_channel, exclude)
            VALUES
                (:name, :channel, :exclude)';
        $stmt = $database->prepare($sql);

        if ($info->dependencies['required']->php->exclude) {
            foreach ($info->dependencies['required']->php->exclude as $exclude) {
                $stmt->bindValue(':name', $n);
                $stmt->bindValue(':channel', $c);
                $stmt->bindValue(':exclude', $exclude);
                $stmt->execute();
            }
        }
        $stmt->close();

        $sql = '
            INSERT INTO pearinstaller_dependencies
              (packages_name, packages_channel, min, max)
            VALUES
                (:name, :channel, :min, :max)';

        $max = $info->dependencies['required']->pearinstaller->max;
        $stmt = $database->prepare($sql);

        $stmt->bindValue(':name', $n);
        $stmt->bindValue(':channel', $c);
        $stmt->bindValue(':min', $info->dependencies['required']->pearinstaller->min);
        if ($max === null) {
            $stmt->bindValue(':max', $max, SQLITE3_NULL);
        } else {
            $stmt->bindValue(':max', $max);
        }
        $stmt->execute();
        $stmt->close();

        $sql = '
            INSERT INTO pearinstaller_dependencies_exclude
              (packages_name, packages_channel, exclude)
            VALUES
                (:name, :channel, :exclude)';
        $stmt = $database->prepare($sql);

        if ($info->dependencies['required']->pearinstaller->exclude) {
            foreach ($info->dependencies['required']->pearinstaller->exclude as $exclude) {
                $stmt->bindValue(':name', $n);
                $stmt->bindValue(':channel', $c);
                $stmt->bindValue(':exclude', $exclude);
                $stmt->execute();
            }
        }
        $stmt->close();

        if (isset($info->dependencies['required']->os)) {
            $sql = '
                INSERT INTO os_dependencies
                  (packages_name, packages_channel, osname, conflicts)
                VALUES
                    (:name, :channel, :os, :conflicts)';
            $stmt = $database->prepare($sql);

            foreach ($info->dependencies['required']->os as $dep) {

                $stmt->clear();
                $stmt->bindValue(':name', $n);
                $stmt->bindValue(':channel', $c);
                $stmt->bindValue(':os', $dep->name);
                $stmt->bindValue(':conflicts', $dep->conflicts, SQLITE3_INTEGER);
                $stmt->execute();
            }
            $stmt->close();
        }

        if (isset($info->dependencies['required']->arch)) {
            $sql = '
                INSERT INTO arch_dependencies
                  (packages_name, packages_channel, pattern, conflicts)
                VALUES
                    (:name, :channel, :arch, :conflicts)';

            $stmt = $database->prepare($sql);
            foreach ($info->dependencies['required']->arch as $dep) {

                $stmt->clear();
                $stmt->bindValue(':name', $n);
                $stmt->bindValue(':channel', $c);
                $stmt->bindValue(':arch', $dep->pattern);
                $stmt->bindValue(':conflicts', $dep->conflicts, SQLITE3_INTEGER);
                $stmt->execute();
            }
            $stmt->close();
        }

        foreach ($info->dependencies['group'] as $group) {
            $sql = '
                INSERT INTO dep_groups
                    (packages_name, packages_channel, groupname, grouphint)
                VALUES
                    (:name, :channel, :groupname, :grouphint)';

            $stmt = $database->prepare($sql);
            $stmt->bindValue(':name', $n);
            $stmt->bindValue(':channel', $c);
            $stmt->bindValue(':groupname', $group->name);
            $stmt->bindValue(':grouphint', $group->hint);

            $stmt->execute();
            $stmt->close();

            $sql = '
                INSERT INTO extension_dependencies
                    (required, packages_name, packages_channel, extension,
                     conflicts, min, max, recommended, groupname)
                VALUES
                    (0, :name, :channel, :extension,
                     :conflicts, :min, :max, :recommended, :groupname)';

            $stmt = $database->prepare($sql);
            foreach ($group->extension as $d) {
                // $d is a \Pyrus\PackageFile\v2\Dependencies\Package object

                $stmt->clear();
                $stmt->bindValue(':name', $n);
                $stmt->bindValue(':channel', $c);
                $stmt->bindValue(':extension', $d->name);
                $stmt->bindValue(':conflicts', $d->conflicts, SQLITE3_INTEGER);
                $stmt->bindValue(':min', $d->min);
                $stmt->bindValue(':max', $d->max);
                $stmt->bindValue(':recommended', $d->recommended);
                $stmt->bindValue(':groupname', $group->name);

                $stmt->execute();

                if (isset($d->exclude)) {
                    $sql = '
                        INSERT INTO extension_dependencies_exclude
                         (required, packages_name, packages_channel,
                          extension, exclude, conflicts, groupname)
                        VALUES(0, :name, :channel, :extension,
                               :exclude, :conflicts, :groupname)';

                    $stmt1 = $database->prepare($sql);
                    foreach ($d->exclude as $exclude) {
                        $stmt1->clear();
                        $stmt1->bindValue(':name', $n);
                        $stmt1->bindValue(':channel', $c);
                        $stmt1->bindValue(':extension', $d->name);
                        $stmt1->bindValue(':exclude', $exclude);
                        $stmt1->bindValue(':conflicts', $d->conflicts, SQLITE3_INTEGER);
                        $stmt1->bindValue(':groupname', $group->name);

                        $stmt1->execute();
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

            $stmt = $database->prepare($sql);
            foreach (array('package', 'subpackage') as $package) {
                foreach ($group->$package as $d) {
                    // $d is a \Pyrus\PackageFile\v2\Dependencies\Package object
                    $sub          = $package == 'subpackage';
                    $ext          = $d->providesextension;

                    $stmt->clear();
                    $stmt->bindValue(':name', $n);
                    $stmt->bindValue(':channel', $c);
                    $stmt->bindValue(':dep_package', $d->name);
                    $stmt->bindValue(':dep_channel', $d->channel);
                    $stmt->bindValue(':conflicts', $d->conflicts, SQLITE3_INTEGER);
                    $stmt->bindValue(':min', $d->min);
                    $stmt->bindValue(':max', $d->max);
                    $stmt->bindValue(':recommended', $d->recommended);
                    $stmt->bindValue(':sub', $sub);
                    $stmt->bindValue(':group', $group->name);
                    if ($ext) {
                        $stmt->bindValue(':ext', $ext);
                    } else {
                        $stmt->bindValue(':ext', $ext, SQLITE3_NULL);
                    }

                    $stmt->execute();

                    if (isset($d->exclude)) {

                        $sql = '
                            INSERT INTO package_dependencies_exclude
                             (required, packages_name, packages_channel,
                              deppackage, depchannel, exclude, conflicts, is_subpackage, groupname)
                            VALUES(0, :name, :channel, :dep_package,
                                :dep_channel, :exclude, :conflicts, :sub, :group)';

                        $stmt1 = $database->prepare($sql);
                        foreach ($d->exclude as $exclude) {
                            $stmt1->clear();
                            $req = 0;
                            $stmt1->bindValue(':required', $req, SQLITE3_INTEGER);
                            $stmt1->bindValue(':name',        $n);
                            $stmt1->bindValue(':channel',     $c);
                            $stmt1->bindValue(':dep_package', $d->name);
                            $stmt1->bindValue(':dep_channel', $d->channel);
                            $stmt1->bindValue(':exclude',     $exclude);
                            $stmt1->bindValue(':sub', $sub);
                            $stmt1->bindValue(':group', $group->name);
                            $stmt1->bindValue(':conflicts', $d->conflicts, SQLITE3_INTEGER);

                            $stmt1->execute();
                        }
                        $stmt1->close();
                    }
                }
            }
        }
        $stmt->close();
    }

    function uninstall($package, $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot uninstall package, registry is read-only');
        }
        $this->_prepareWrite();
        $database = static::getRegistry($this->_path);

        $channel = Config::current()->channelregistry[$channel]->name;
        if (!$this->exists($package, $channel)) {
            throw new Exception('Unknown package ' . $channel . '/' .
                $package);
        }

        $sql = 'DELETE FROM packages WHERE lcname = "' .
              $database->escapeString(strtolower($package)) . '" AND channel = "' .
              $database->escapeString($channel) . '"';

        $database->enableExceptions(true);
        try {
            $database->exec($sql);
        } catch (\Exception $e) {
            throw new Exception('uninstalling ' . $channel . '/' . $package . ' failed: ' .
                                                     $e->getMessage(), $e);
        }
        $database->enableExceptions(false);
    }

    function exists($package, $channel)
    {
        if (!static::existsRegistry($this->_path)) {
             return false;
        }
        $database = static::getRegistry($this->_path);

        $sql = 'SELECT
                    COUNT(lcname)
                FROM packages
                WHERE
                    lcname = :name AND channel = :channel
            ';
        $stmt = $database->prepare($sql);
        $stmt->bindValue(':name',    strtolower($package));
        $stmt->bindValue(':channel', $channel);
        $result = @$stmt->execute();

        if (!$result) {
            $error = $database->lastErrorMsg();
            throw new Exception('Cannot search for package ' . $channel . '/' . $package .
                ': ' . $error);
        }
        $ret = $result->fetchArray(SQLITE3_NUM);
        return $ret[0];
    }

    function info($package, $channel, $field)
    {
        if (!static::existsRegistry($this->_path)) {
            throw new Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }
        $database = static::getRegistry($this->_path);

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

            $stmt = $database->prepare($sql);
            $stmt->bindValue(':name',    strtolower($package));
            $stmt->bindValue(':channel', $channel);
            $result = @$stmt->execute();

            if (!$result) {
                $error = $database->lastErrorMsg();
                throw new Exception('Cannot retrieve ' . $field .
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
            lcname = \'' . $database->escapeString(strtolower($package)) . '\' AND
            channel = \'' . $database->escapeString($channel) . '\'';

        $info = @$database->querySingle($sql);
        if ($database->lastErrorCode()) {
            $error = $database->lastErrorMsg();
            throw new Exception('Cannot retrieve ' . $field .
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
        if (!static::existsRegistry($this->_path)) {
            return array();
        }
        $database = static::getRegistry($this->_path);

        $ret = array();
        $sql = 'SELECT name FROM packages WHERE channel = :channel ORDER BY name';
        $stmt = $database->prepare($sql);
        $stmt->bindValue(':channel', $channel);
        $result = @$stmt->execute();

        while ($res = $result->fetchArray(SQLITE3_NUM)) {
            $ret[] = $res[0];
        }

        return $ret;
    }

    function __get($var)
    {
        if ($var === 'package') {
            return new Sqlite3\Package($this);
        }
    }

    /**
     * Extract a packagefile object from the registry
     * @return \Pyrus\PackageFile\v2
     */
    function toPackageFile($package, $channel)
    {
        if (!static::existsRegistry($this->_path)) {
            throw new Exception('Error: no existing SQLite3 registry for ' . $this->_path);
        }

        if (!$this->exists($package, $channel)) {
            throw new Exception('Cannot retrieve package file object ' .
                'for package ' . $channel . '/' . $package . ', it is not installed');
        }

        $ret = new \Pyrus\PackageFile\v2;
        $ret->name        = $this->info($package, $channel, 'name');
        $ret->channel     = $channel;
        $ret->summary     = $this->info($package, $channel, 'summary');
        $ret->description = $this->info($package, $channel, 'description');
        $ret->type = $this->info($package, $channel, 'packagetype');

        $sql = 'SELECT * FROM maintainers
                WHERE packages_name = :name AND packages_channel = :channel';

        $database = static::getRegistry($this->_path);
        $stmt = $database->prepare($sql);
        $stmt->bindValue(':name',    strtolower($package));
        $stmt->bindValue(':channel', $channel);
        $result = @$stmt->execute();

        if (!$result) {
            throw new Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $ret->name . ', no maintainers registered');
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

        $stmt = $database->prepare($sql);
        $stmt->bindValue(':name',    strtolower($package));
        $stmt->bindValue(':channel', $channel);
        $result = @$stmt->execute();

        if (!$result) {
            throw new Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $ret->name . ', no files registered');
        }

        while ($file = $result->fetchArray(SQLITE3_ASSOC)) {
            $ret->files[$file['origpath']] = array_merge(
                                                array('attribs' => array('role' => $file['role'])),
                                                unserialize($file['tasks']));
            if ($file['baseinstalldir']) {
                $ret->setFileAttribute($file['origpath'], 'baseinstalldir', $file['baseinstalldir']);
            }

            if ($file['md5sum'] != md5('')) {
                $ret->setFileAttribute($file['origpath'], 'md5sum', $file['md5sum']);
            }
        }
        $stmt->close();

        $sql = 'SELECT dirname, baseinstall FROM baseinstalldirs
                WHERE packages_name = :name AND packages_channel = :channel';

        $stmt = $database->prepare($sql);
        $stmt->bindValue(':name',    strtolower($package));
        $stmt->bindValue(':channel', $channel);
        $result = @$stmt->execute();

        if (!$result) {
            throw new Exception('Could not retrieve package file object' .
                ' for package ' . $channel . '/' . $ret->name . ', no files registered');
        }

        $dirs = array();
        while ($dir = $result->fetchArray(SQLITE3_ASSOC)) {
            $dirs[$dir['dirname']] = $dir['baseinstall'];
        }
        $ret->setBaseInstallDirs($dirs);
        $stmt->close();
        $this->fetchCompatible($ret);
        $this->fetchDeps($ret);

        $ret->release = null;
        $sql = 'SELECT * FROM configureoptions
                WHERE
                    packages_name = "' . $database->escapeString(strtolower($package)) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($option = $a->fetchArray(SQLITE3_ASSOC)) {
            $ret->installrelease->configureoption[$option['name']]
                                ->prompt($option['prompt'])
                                ->default($option['defaultValue']);
        }
        return $ret;
    }

    function fetchCompatible(\Pyrus\PackageFileInterface $ret)
    {
        $database = static::getRegistry($this->_path);
        $package = $ret->name;
        $channel = $ret->channel;
        $sql = 'SELECT * FROM compatible_releases
                WHERE
                    packages_name = "' . $database->escapeString(strtolower($package)) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray(SQLITE3_ASSOC)) {
            $ret->compatible[$dep['compat_channel'] . '/' . $dep['compat_package']]->min =
                $dep['min'];
            $ret->compatible[$dep['compat_channel'] . '/' . $dep['compat_package']]->max =
                $dep['max'];
        }

        $sql = 'SELECT * FROM compatible_releases_exclude
                WHERE
                    packages_name = "' . $database->escapeString(strtolower($package)) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray(SQLITE3_ASSOC)) {
            $ret->compatible[$dep['compat_channel'] . '/' . $dep['compat_package']]->exclude =
                $dep['exclude'];
        }
    }

    function fetchDeps(\Pyrus\PackageFileInterface $ret)
    {
        $database = static::getRegistry($this->_path);
        $package = strtolower($ret->name);
        $channel = $ret->channel;
        $sql = 'SELECT * FROM php_dependencies
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->php->min = $dep['min'];
            $ret->dependencies['required']->php->max = $dep['max'];
        }

        $sql = 'SELECT * FROM php_dependencies_exclude
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->php->exclude($dep['exclude']);
        }

        $sql = 'SELECT * FROM pearinstaller_dependencies
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->pearinstaller->min = $dep['min'];
            $ret->dependencies['required']->pearinstaller->max = $dep['max'];
        }

        $sql = 'SELECT * FROM pearinstaller_dependencies_exclude
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->pearinstaller->exclude($dep['exclude']);
        }

        $sql = 'SELECT * FROM os_dependencies
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray(SQLITE3_ASSOC)) {
            $ret->dependencies['required']->os[$dep['osname']] = !$dep['conflicts'];
            $rawdeps = $ret->rawdeps;
        }

        $sql = 'SELECT * FROM arch_dependencies
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $a = $database->query($sql);

        while ($dep = $a->fetchArray()) {
            $ret->dependencies['required']->arch[$dep['pattern']] = !$dep['conflicts'];
        }

        $sql = 'SELECT * FROM package_dependencies
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
                //ORDER BY required, deppackage, depchannel, conflicts';
        $package_deps = $database->query($sql);

        $sql = 'SELECT * FROM package_dependencies_exclude
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
                //ORDER BY required, deppackage, depchannel, conflicts, exclude';
        $excludes = $database->query($sql);
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
            $package  = $dep['is_subpackage'] ? 'subpackage' : 'package';
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

    function fetchDepGroups(\Pyrus\PackageFileInterface $ret)
    {
        $database = static::getRegistry($this->_path);
        $package = strtolower($ret->name);
        $channel = $ret->channel;

        $sql = 'SELECT * FROM dep_groups
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $groups = $database->query($sql);
        if ($groups) {
            while ($group = $groups->fetchArray(SQLITE3_ASSOC)) {
                $ret->dependencies['group']->{$group['groupname']}->hint = $group['grouphint'];
            }
        }

        return $ret;
    }

    function fetchExtensionDeps(\Pyrus\PackageFileInterface $ret)
    {
        $database = static::getRegistry($this->_path);
        $package = strtolower($ret->name);
        $channel = $ret->channel;
        $sql = 'SELECT * FROM extension_dependencies
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $extension_deps = $database->query($sql);

        $sql = 'SELECT * FROM extension_dependencies_exclude
                WHERE
                    packages_name = "' . $database->escapeString($package) . '" AND
                    packages_channel = "' . $database->escapeString($channel) . '"';
        $excludes = $database->query($sql);
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

    public function getDependentPackages(\Pyrus\PackageFileInterface $package, $minimal = true)
    {
        if (!$this->_initialized) {
            return array();
        }

        if (!static::existsRegistry($this->_path)) {
            throw new \Pyrus\ChannelRegistry\Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }
        $database = static::getRegistry($this->_path);

        $ret = array();
        $sql = 'SELECT
                    packages_channel, packages_name
                FROM package_dependencies
                WHERE
                    deppackage = :name AND depchannel = :channel
                ORDER BY packages_channel, packages_name';
        $stmt = $database->prepare($sql);
        $stmt->bindValue(':name', $package->name, SQLITE3_TEXT);
        $stmt->bindValue(':channel', $package->channel, SQLITE3_TEXT);
        $result = @$stmt->execute();

        while ($res = $result->fetchArray()) {
            try {
                if ($minimal) {
                    // only retrieve package name and dependencies
                    $pf = new \Pyrus\PackageFile\v2;
                    $pf->name    = $this->info($res[1], $res[0], 'name');
                    $pf->channel = $res[0];
                    $this->fetchDeps($pf);
                    $ret[] = $pf;
                } else {
                    $ret[] = $this->package[$res[0] . '/' . $res[1]];
                }
            } catch (\Exception $e) {
                throw new \Pyrus\ChannelRegistry\Exception('Could not retrieve ' .
                    'dependent package ' . $res[0] . '/' . $res[1], $e);
            }
        }

        return $ret;
    }

    /**
     * Detect any files already installed that would be overwritten by
     * files inside the package represented by $package
     */
    public function detectFileConflicts(\Pyrus\PackageFileInterface $package)
    {
        if (!static::existsRegistry($this->_path)) {
            throw new \Pyrus\ChannelRegistry\Exception('Error: no existing SQLite3 channel registry for ' . $this->_path);
        }
        $database = static::getRegistry($this->_path);

        $ret = array();
        $sql = 'SELECT
                    packages_channel, packages_name
                FROM files
                WHERE
                    packagepath = :path
                ORDER BY packages_channel, packages_name';
        $stmt = $database->prepare($sql);
        // now iterate over each file in the package, and note all the conflicts
        $roles = array();
        foreach (Role::getValidRoles($package->getPackageType()) as $role) {
            // set up a list of file role => configuration variable
            // for storing in the registry
            $roles[$role] = Role::factory($package->getPackageType(), $role);
        }

        $ret = array();
        $config = Config::current();
        foreach ($package->installcontents as $file) {
            $stmt->reset();
            $relativepath = $roles[$file->role]->getRelativeLocation($package, $file);
            if (!$relativepath) {
                continue;
            }

            $testpath = $config->{$roles[$file->role]->getLocationConfig()} .
                    DIRECTORY_SEPARATOR . $relativepath;
            $stmt->bindValue(':path', $testpath, SQLITE3_TEXT);
            $result = $stmt->execute();

            while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
                $pn = $this->info($res['packages_name'], $res['packages_channel'], 'name');
                $ret[] = array($relativepath => $res['packages_channel'] . '/' . $pn);
            }
        }

        return $ret;
    }

    /**
     * Returns a list of registries present in the PEAR installation at $path
     *
     * @param string $path Path to where the registry is stored
     *
     * @return array
     */
    static public function detectRegistries($path)
    {
        if (isset(Main::$options['packagingroot'])) {
            $path = Main::prepend(Main::$options['packagingroot'], $path);
        }

        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        if (file_exists($path . DIRECTORY_SEPARATOR . '.pear2registry') || is_file($path . DIRECTORY_SEPARATOR . '.pear2registry')) {
            return array('Sqlite3');
        }

        return array();
    }

    /**
     * Completely remove all traces of an sqlite3 registry
     *
     * @param string $path Path to where the registry is stored
     */
    static public function removeRegistry($path)
    {
        if ($path === ':memory:') {
            unset(static::$databases[$path]);
            return;
        }

        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
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
            throw new Exception('Cannot remove Sqlite3 registry: Unable to remove SQLite database');
        }
    }

    static public function existsRegistry($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        return isset(static::$databases[$path]);
    }

    /**
     * Returns the registry database object for the given registry file path
     *
     * @param string $path Path to the registry file
     *
     * @return SQLite3 Database object
     */
    static public function getRegistry($path)
    {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        if (!static::existsRegistry($path)) {
            throw new \Pyrus\ChannelRegistry\Exception('Error: no existing SQLite3 channel registry for ' . $path);
        }
        return static::$databases[$path];
    }

    static public function initRegistry($path, $readonly)
    {
        if ($path && $path != ':memory:') {
            $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
            if (dirname($path) . DIRECTORY_SEPARATOR . '.pear2registry' != $path) {
                $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
            }
        }
        if ($path && file_exists($path)) {
            static::_init($path, $readonly);
            return $path;
        }

        if (!$path || $path == ':memory:') {
            if ($readonly) {
                throw new Exception('Cannot create SQLite3 registry, registry is read-only');
            }

            $path = ':memory:';
            static::_init($path, $readonly);
            return $path;
        }

        $file = $path;
        while ($file && $file !== '.' && $file !== '/' && !is_writable($file)) {

            if (dirname($file) === $file) {
                $file = false;
                break;
            }

            $file = dirname($file);
        }

        if (!$file || $file == '.') {
            throw new Exception('Cannot create SQLite3 registry, registry is read-only');
        }

        static::_init($path, $readonly);
        return $path;
    }

    static private function _init($path, $readonly)
    {
        if (!$path) {
            $path = ':memory:';
        } else {
            $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        }

        if (static::existsRegistry($path)) {
            return;
        }

        $dbpath = $path;
        if ($path != ':memory:' && isset(Main::$options['packagingroot'])) {
            $dbpath = Main::prepend(Main::$options['packagingroot'], $path);
        }

        if ($path != ':memory:' && !file_exists(dirname($dbpath))) {
            if ($readonly) {
                throw new Exception('Cannot create SQLite3 registry, registry is read-only');
            }
            @mkdir(dirname($dbpath), 0755, true);
        }

        if ($readonly && $path != ':memory:' && !file_exists($dbpath)) {
            throw new Exception('Cannot create SQLite3 registry, registry is read-only');
        }

        static::$databases[$path] = new \SQLite3($dbpath);
        // ScottMac needs to fix sqlite3 FIXME
        if (static::$databases[$path]->lastErrorCode()) {
            $error = static::$databases[$path]->lastErrorMsg();
            throw new Exception('Cannot open SQLite3 registry: ' . $error);
        }

        $sql = 'SELECT version FROM pearregistryversion';
        if (@static::getRegistry($path)->querySingle($sql) == '1.0.0') {
            return;
        }

        if ($readonly) {
            throw new Exception('Cannot create SQLite3 registry, registry is read-only');
        }

        $a = new Sqlite3\Creator;
        try {
            $a->create(static::$databases[$path]);
        } catch (\Exception $e) {
            unset(static::$databases[$path]);
            $a = get_class($e);
            throw new $a('Database initialization failed', 0, $e);
        }
    }

    function begin()
    {
        if (!static::existsRegistry($this->_path)) {
            static::_init($this->_path, $this->readonly);
        }

        static::getRegistry($this->_path)->exec('BEGIN');
    }

    function rollback()
    {
        static::getRegistry($this->_path)->exec('ROLLBACK');
    }

    function commit()
    {
        static::getRegistry($this->_path)->exec('COMMIT');
    }
}