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
     * The database resource
     *
     * @var SQLiteDatabase
     */
    protected $database;
    private $_path;

    /**
     * Initialize the registry
     *
     * @param unknown_type $path
     */
    function __construct($path)
    {
        if ($path) {
            if ($path != ':memory:') {
                if (dirname($path) . DIRECTORY_SEPARATOR . '.pear2registry' != $path) {
                    $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
                }
            }
        }
        $this->_init($path);
        $this->_path = $path;
    }

    private function _init($path)
    {
        $error = '';
        if (!$path) {
            $path = ':memory:';
        } elseif (!file_exists(dirname($path))) {
            @mkdir(dirname($path), 0755, true);
        }
        $this->database = new SQLiteDatabase($path, 0666, $error);
        if (!$this->database) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot open SQLite registry: ' . $error);
        }
        if (@$this->database->singleQuery('SELECT version FROM pearregistryversion') == '1.0.0') {
            return;
        }
        $a = new PEAR2_Pyrus_Registry_Sqlite_Creator;
        $a->create($this->database);
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
        try {
            // this ensures upgrade will work
            $this->uninstall($info->name, $info->channel);
        } catch (Exception $e) {
            // ignore errors
        }
        $this->database->queryExec('BEGIN');
        $licloc = $info->license;
        $licuri = isset($licloc['attribs']['uri']) ? '"' .
            sqlite_escape_string($licloc['attribs']['uri']) . '"' : 'NULL';
        $licpath = isset($licloc['attribs']['path']) ? '"' .
            sqlite_escape_string($licloc['attribs']['path']) . '"' : 'NULL';
        if (!@$this->database->queryExec('
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
            $this->database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->channel . '/' . $info->name . ' could not be installed in registry');
        }
        foreach ($info->allmaintainers as $role => $maintainers) {
            if (!is_array($maintainers)) continue;
            foreach ($maintainers as $maintainer) {
                if (!@$this->database->queryExec('
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
                    $this->database->queryExec('ROLLBACK');
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
            if (!@$this->database->queryExec('
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
                $this->database->queryExec('ROLLBACK');
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
                    if (!@$this->database->queryExec('
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
                        $this->database->queryExec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->getName() . ' could not be installed in registry');
                    }
                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }
                        foreach ($d['exclude'] as $exclude) {
                            if (!@$this->database->queryExec('
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
                                $this->database->queryExec('ROLLBACK');
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
                    if (!@$this->database->queryExec('
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
                        $this->database->queryExec('ROLLBACK');
                        throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                            $info->channel . '/' . $info->name . ' could not be installed in registry');
                    }
                    if (isset($d['exclude'])) {
                        if (!is_array($d['exclude'])) {
                            $d['exclude'] = array($d['exclude']);
                        }
                        foreach ($d['exclude'] as $exclude) {
                            if (!@$this->database->queryExec('
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
                                $this->database->queryExec('ROLLBACK');
                                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                    $info->channel . '/' . $info->name . ' could not be installed in registry');
                            }                        
                        }
                    }
                }
            }
        }
        $this->database->queryExec('COMMIT');
    }

    function uninstall($package, $channel)
    {
        $channel = PEAR2_Pyrus_Config::current()->channelregistry[$channel]->getName();
        if (!$this->database->singleQuery('SELECT name FROM packages WHERE name="' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"')) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel . '/' .
                $package);
        }
        $this->database->queryExec('DELETE FROM packages WHERE name="' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"');
    }

    function exists($package, $channel)
    {
        return $this->database->singleQuery('SELECT COUNT(*) FROM packages WHERE ' .
            'name=\'' . sqlite_escape_string($package) . '\' AND channel=\'' .
            sqlite_escape_string($channel) . '\'');
    }

    function info($package, $channel, $field)
    {
        $info = @$this->database->singleQuery('
            SELECT ' . $field . ' FROM packages WHERE
            name = \'' . sqlite_escape_string($package) . '\' AND
            channel = \'' . sqlite_escape_string($channel) . '\'', true);
        if (!$info) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                ': ' . sqlite_error_string($this->database->lastError()));
        }
        return $info;
    }

    function listPackages($channel)
    {
        $ret = array();
        foreach ($this->database->arrayQuery('SELECT name FROM packages WHERE
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
