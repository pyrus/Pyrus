<?php
/**
 * This is the central registry, that is used for all installer options,
 * stored as an SQLite database
 * 
 * Registry information that must be stored:
 *
 * - A list of installed packages
 * - the files in each package
 * - known channels
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
                if (dirname($path . '.pear2registry') != $path) {
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
        if ($this->database->singleQuery('SELECT name FROM packages WHERE name="' .
              $info->getName() . '" AND channel="' . $info->getChannel() . '"')) {
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->getChannel() . '/' . $info->getName() . ' has already been installed');
        }
        $this->database->queryExec('BEGIN');
        $licloc = $info->getLicenseLocation();
        $licuri = isset($licloc['uri']) ? '"' .
            sqlite_escape_string($licloc['uri']) . '"' : 'NULL';
        $licpath = isset($licloc['path']) ? '"' .
            sqlite_escape_string($licloc['path']) . '"' : 'NULL';
        if (!@$this->database->queryExec('
             INSERT INTO packages
              (name, channel, version, apiversion, summary,
               description, stability, apistability, releasedate,
               releasetime, license, licenseuri, licensepath,
               releasenotes, lastinstalledversion, installedwithpear,
               installtimeconfig)
             VALUES(
              "' . $info->getName() . '",
              "' . $info->getChannel() . '",
              "' . $info->getVersion('release') . '",
              "' . $info->getVersion('api') . '",
              \'' . sqlite_escape_string($info->getSummary()) . '\',
              \'' . sqlite_escape_string($info->getDescription()) . '\',
              "' . $info->getState('release') . '",
              "' . $info->getState('api') . '",
              "' . $info->getDate() . '",
              ' . ($info->getTime() ? '"' . $info->getTime() . '"' : 'NULL') . ',
              "' . $info->getLicense() . '",
              ' . $licuri . ',
              ' . $licpath . ',
              \'' . sqlite_escape_string($info->getNotes()) . '\',
              NULL,
              "2.0.0",
              "' . PEAR2_Pyrus_Config::configSnapshot() . '"
             )
            ')) {
            $this->database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                $info->getChannel() . '/' . $info->getName() . ' could not be installed in registry');
        }
        foreach ($info->getMaintainers() as $maintainer) {
            if (!@$this->database->queryExec('
                 INSERT INTO maintainers
                  (packages_name, packages_channel, role, user,
                   email, active)
                 VALUES(
                  "' . $info->getName() . '",
                  "' . $info->getChannel() . '",
                  "' . $maintainer['role'] . '",
                  "' . $maintainer['handle'] . '",
                  "' . $maintainer['email'] . '",
                  "' . $maintainer['active'] . '"
                 )
                ')) {
                $this->database->queryExec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->getChannel() . '/' . $info->getName() . ' could not be installed in registry');
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
                  "' . $info->getName() . '",
                  "' . $info->getChannel() . '",
                  "' . $file->name . '",
                  "' . $file->role . '",
                  "' . $curconfig->{$roles[$file->role]} . '"
                 )
                ')) {
                $this->database->queryExec('ROLLBACK');
                throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                    $info->getChannel() . '/' . $info->getName() . ' could not be installed in registry');
            }
        }

        $deps = $info->getDependencies();
        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                if (isset($deps[$required][$package])) {
                    $ds = isset($deps[$required][$package][0]) ?
                        $deps[$required][$package] :
                        array($deps[$required][$package]);
                    foreach ($ds as $d) {
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
                              "' . $info->getName() . '",
                              "' . $info->getChannel() . '",
                              "' . $d['name'] . '",
                              "' . $dchannel . '",
                              "' . isset($d['conflicts']) . '",
                              ' . $dmin . ',
                              ' . $dmax . '
                             )
                            ')) {
                            $this->database->queryExec('ROLLBACK');
                            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                $info->getChannel() . '/' . $info->getName() . ' could not be installed in registry');
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
                                      "' . $info->getName() . '",
                                      "' . $info->getChannel() . '",
                                      "' . $d['name'] . '",
                                      "' . $dchannel . '",
                                      "' . $exclude . '",
                                      "' . isset($d['conflicts']) . '"
                                     )
                                    ')) {
                                    $this->database->queryExec('ROLLBACK');
                                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                        $info->getChannel() . '/' . $info->getName() . ' could not be installed in registry');
                                }                        
                            }
                        }
                    }
                }
            }
        }
        if (!isset($deps['group'])) {
            $deps['group'] = array();
        } elseif (!isset($deps['group'][0])) {
            $deps['group'] = array($deps['group']);
        }
        foreach ($deps['group'] as $group) {
            foreach (array('package', 'subpackage') as $package) {
                if (isset($group[$package])) {
                    $ds = isset($group[$package][0]) ?
                        $group[$package] :
                        array($group[$package]);
                    foreach ($ds as $d) {
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
                              "' . $info->getName() . '",
                              "' . $info->getChannel() . '",
                              "' . $d['name'] . '",
                              "' . $dchannel . '",
                              "' . isset($d['conflicts']) . '",
                              ' . $dmin . ',
                              ' . $dmax . '
                             )
                            ')) {
                            $this->database->queryExec('ROLLBACK');
                            throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                $info->getChannel() . '/' . $info->getName() . ' could not be installed in registry');
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
                                      "' . $info->getName() . '",
                                      "' . $info->getChannel() . '",
                                      "' . $d['name'] . '",
                                      "' . $dchannel . '",
                                      "' . $exclude . '",
                                      "' . isset($d['conflicts']) . '",
                                     )
                                    ')) {
                                    $this->database->queryExec('ROLLBACK');
                                    throw new PEAR2_Pyrus_Registry_Exception('Error: package ' .
                                        $info->getChannel() . '/' . $info->getName() . ' could not be installed in registry');
                                }                        
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
        $channel = $this->aliasToChannel($channel);
        if (!$this->database->singleQuery('SELECT package FROM packages WHERE package="' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"')) {
            throw new PEAR2_Pyrus_Registry_Exception('Unknown package ' . $channel . '/' .
                $package);
        }
        $this->database->queryExec('DELETE FROM packages WHERE package="' .
              sqlite_escape_string($package) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"');
    }

    function upgrade(PEAR2_Pyrus_PackageFile_v2 $info)
    {
        if (!$this->database->singleQuery('SELECT package FROM packages WHERE package="' .
              sqlite_escape_string($info) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"')) {
            return $this->installPackage($info);
        }
        $lastversion = $this->database->singleQuery('
                SELECT version FROM packages WHERE package="' .
              sqlite_escape_string($info) . '" AND channel = "' .
              sqlite_escape_string($channel) . '"');
        $this->uninstallPackage($info->getPackage(), $info->getChannel());
        $this->installPackage($info);
        $this->database->queryExec('UPDATE packages set lastinstalledversion="' .
            sqlite_escape_string($lastversion) . '"');
    }

    function exists($package, $channel)
    {
        return $this->database->singleQuery('SELECT COUNT(*) FROM packages WHERE ' .
            'package=\'' . sqlite_escape_string($package) . '\' AND channel=\'' .
            sqlite_escape_string($channel) . '\'');
    }

    function info($package, $channel, $field)
    {
        $info = @$this->database->singleQuery('
            SELECT ' . $field . ' FROM packages WHERE
            package = \'' . sqlite_escape_string($package) . '\' AND
            channel = \'' . sqlite_escape_string($channel) . '\'', true);
        if (!$info) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot retrieve ' . $field .
                ': ' . $this->database->error_string($this->database->lastError()));
        }
        return $info;
    }
}
