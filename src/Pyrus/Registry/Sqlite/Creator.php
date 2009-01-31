<?php
/**
 * PEAR2_Pyrus_Registry_Sqlite_Creator
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
 * Initialize a sqlite registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Sqlite_Creator
{
    /**
     * Initialize the database for the registry
     *
     * Registry information that must be stored:
     *
     * - A list of installed packages
     * - the files in each package
     * - known channels
     *
     * The SQLite database has this structure:
     *
     * <pre>
     * CREATE TABLE packages (
     *  name TEXT(80) NOT NULL,
     *  channel TEXT(255) NOT NULL,
     *  version TEXT(20) NOT NULL,
     *  apiversion TEXT(20) NOT NULL,
     *  summary TEXT NOT NULL,
     *  description TEXT NOT NULL,
     *  stability TEXT(8) NOT NULL,
     *  apistability TEXT(8) NOT NULL,
     *  releasedate DATE NOT NULL,
     *  releasetime TIME,
     *  license TEXT(50) NOT NULL,
     *  licenseuri TEXT,
     *  licensepath TEXT,
     *  releasenotes TEXT,
     *  lastinstalledversion TEXT(20),
     *  installedwithpear TEXT(20),
     *  installtimeconfig TEXT(50), -- the path to configuration as stored
     *  PRIMARY KEY (name, channel)
     * );
     *
     * CREATE TABLE maintainers (
     *  packages_name TEXT(80) NOT NULL,
     *  packages_channel TEXT(255) NOT NULL,
     *  role TEXT(11) NOT NULL,
     *  user TEXT(20) NOT NULL,
     *  name TEXT(200) NOT NULL,
     *  email TEXT(100) NOT NULL,
     *  active CHAR(3) NOT NULL,
     *  PRIMARY KEY (packages_name, packages_channel, user)
     * );
     *
     * CREATE TABLE files (
     *  packages_name TEXT(80) NOT NULL,
     *  packages_channel TEXT(255) NOT NULL,
     *  packagepath TEXT(255) NOT NULL,
     *  role TEXT(30) NOT NULL,
     *  rolepath TEXT(255) NOT NULL,
     *  PRIMARY KEY (packagepath, role, rolepath),
     *  UNIQUE (packages_name, packages_channel, packagepath)
     * );
     *
     * CREATE TABLE package_dependencies (
     *  required BOOL NOT NULL,
     *  packages_name TEXT(80) NOT NULL,
     *  packages_channel TEXT(255) NOT NULL,
     *  deppackage TEXT(80) NOT NULL,
     *  depchannel TEXT(255) NOT NULL,
     *  conflicts BOOL NOT NULL,
     *  min TEXT(20),
     *  max TEXT(20),
     *  PRIMARY KEY (required, packages_name, packages_channel, deppackage, depchannel)
     * );
     *
     * CREATE TABLE package_dependencies_exclude (
     *  required BOOL NOT NULL,
     *  packages_name TEXT(80) NOT NULL,
     *  packages_channel TEXT(255) NOT NULL,
     *  deppackage TEXT(80) NOT NULL,
     *  depchannel TEXT(255) NOT NULL,
     *  conflicts BOOL NOT NULL,
     *  exclude TEXT(20),
     *  PRIMARY KEY (required, packages_name, packages_channel, deppackage, depchannel)
     * );
     *
     * CREATE TABLE channels (
     *  channel TEXT NOT NULL,
     *  summary TEXT NOT NULL,
     *  suggestedalias TEXT(50) NOT NULL,
     *  alias TEXT(50) NOT NULL,
     *  validatepackageversion TEXT(20) NOT NULL default "default",
     *  validatepackage NOT NULL default "PEAR_Validate",
     *  lastmodified DATETIME,
     *  PRIMARY KEY (channel),
     *  UNIQUE(alias)
     * );
     *
     * CREATE TABLE channel_servers (
     *  channel TEXT NOT NULL,
     *  server TEXT NOT NULL,
     *  ssl integer NOT NULL default 0,
     *  port integer NOT NULL default 80,
     *  PRIMARY KEY (channel, server)
     * );
     *
     * CREATE TABLE channel_server_rest (
     *  channel TEXT NOT NULL,
     *  server TEXT NOT NULL,
     *  type TEXT NOT NULL,
     *  baseurl TEXT NOT NULL,
     *  PRIMARY KEY (channel, server, baseurl, type)
     * );
     *
     * CREATE TABLE pearregistryversion (
     *  version TEXT(20) NOT NULL default "1.0.0"
     * );
     *
     * INSERT INTO pearregistryversion VALUES("1.0.0");
     *
     * CREATE TRIGGER package_delete DELETE ON packages
     *   FOR EACH ROW BEGIN
     *     DELETE FROM maintainers
     *     WHERE
     *       maintainers.packages_name = old.name AND
     *       maintainers.packages_channel = old.channel;
     *     DELETE FROM files
     *     WHERE
     *       files.packages_name = old.name AND
     *       files.packages_channel = old.channel;
     *     DELETE FROM package_dependencies
     *     WHERE
     *       package_dependencies.packages_name = old.name AND
     *       package_dependencies.packages_channel = old.channel;
     *     DELETE FROM package_dependencies_exclude
     *     WHERE
     *       package_dependencies_exclude.packages_name = old.name AND
     *       package_dependencies_exclude.packages_channel = old.channel;
     *   END;
     *
     * CREATE TRIGGER channel_delete DELETE ON channels
     *   FOR EACH ROW BEGIN
     *     DELETE FROM channel_servers
     *     WHERE
     *       channel_servers.channel = old.channel;
     *     DELETE FROM channel_server_rest
     *     WHERE
     *       channel_server_rest.channel = old.channel;
     *   END;
     * CREATE VIEW deps AS
     *   SELECT
     *       packages_name,
     *       packages_channel
     *       deppackage,
     *       depchannel,
     *       null as exclude,
     *       conflicts,
     *       min,
     *       max
     *   FROM package_dependencies
     *   UNION
     *   SELECT
     *       packages_name,
     *       packages_channel
     *       deppackage,
     *       depchannel,
     *       exclude,
     *       conflicts,
     *       null as min,
     *       null as max
     *   FROM package_dependencies_exclude
     *
     * CREATE VIEW protocols AS
     *  SELECT
     *      channel,
     *      server,
     *      baseurl as function,
     *      type as version,
     *      "rest" as protocol
     *  FROM channel_server_rest
     *
     * </pre>
     */
    function create(SQLiteDatabase $database)
    {
        if (!$database->queryExec('BEGIN', $error)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
          CREATE TABLE packages (
           name TEXT(80) NOT NULL,
           channel TEXT(255) NOT NULL,
           version TEXT(20) NOT NULL,
           apiversion TEXT(20) NOT NULL,
           summary TEXT NOT NULL,
           description TEXT NOT NULL,
           stability TEXT(8) NOT NULL,
           apistability TEXT(8) NOT NULL,
           releasedate DATE NOT NULL,
           releasetime TIME,
           license TEXT(50) NOT NULL,
           licenseuri TEXT,
           licensepath TEXT,
           releasenotes TEXT,
           lastinstalledversion TEXT(20),
           installedwithpear TEXT(20),
           installtimeconfig TEXT(50),
           PRIMARY KEY (name, channel)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE maintainers (
           packages_name TEXT(80) NOT NULL,
           packages_channel TEXT(255) NOT NULL,
           role TEXT(11) NOT NULL,
           name TEXT(200) NOT NULL,
           user TEXT(20) NOT NULL,
           email TEXT(100) NOT NULL,
           active CHAR(3) NOT NULL,
           PRIMARY KEY (packages_name, packages_channel, user)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE files (
           packages_name TEXT(80) NOT NULL,
           packages_channel TEXT(255) NOT NULL,
           packagepath TEXT(255) NOT NULL,
           role TEXT(30) NOT NULL,
           rolepath TEXT(255) NOT NULL,
           PRIMARY KEY (packagepath, role, rolepath),
           UNIQUE (packages_name, packages_channel, packagepath)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE package_dependencies (
           required BOOL NOT NULL,
           packages_name TEXT(80) NOT NULL,
           packages_channel TEXT(255) NOT NULL,
           deppackage TEXT(80) NOT NULL,
           depchannel TEXT(255) NOT NULL,
           conflicts BOOL NOT NULL,
           min TEXT(20),
           max TEXT(20),
           PRIMARY KEY (required, packages_name, packages_channel, deppackage, depchannel)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE package_dependencies_exclude (
           required BOOL NOT NULL,
           packages_name TEXT(80) NOT NULL,
           packages_channel TEXT(255) NOT NULL,
           deppackage TEXT(80) NOT NULL,
           depchannel TEXT(255) NOT NULL,
           exclude TEXT(20),
           conflicts BOOL NOT NULL,
           PRIMARY KEY (required, packages_name, packages_channel, deppackage, depchannel)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE channels (
           channel TEXT NOT NULL,
           summary TEXT NOT NULL,
           suggestedalias TEXT(50) NOT NULL,
           alias TEXT(50) NOT NULL,
           validatepackageversion TEXT(20) NOT NULL default "default",
           validatepackage NOT NULL default "PEAR_Validate",
           lastmodified TEXT,
           PRIMARY KEY (channel),
           UNIQUE(alias)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE channel_servers (
           channel TEXT NOT NULL,
           server TEXT NOT NULL,
           ssl integer NOT NULL default 0,
           port integer NOT NULL default 80,
           PRIMARY KEY (channel, server)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE channel_server_rest (
           channel TEXT NOT NULL,
           server TEXT NOT NULL,
           type TEXT NOT NULL,
           baseurl TEXT NOT NULL,
           PRIMARY KEY (channel, server, baseurl, type)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE pearregistryversion (
           version TEXT(20) NOT NULL
          );

          INSERT INTO pearregistryversion VALUES("1.0.0");
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TRIGGER package_delete DELETE ON packages
            FOR EACH ROW BEGIN
              DELETE FROM maintainers
              WHERE
                maintainers.packages_name = old.name AND
                maintainers.packages_channel = old.channel;
              DELETE FROM files
              WHERE
                files.packages_name = old.name AND
                files.packages_channel = old.channel;
              DELETE FROM package_dependencies
              WHERE
                package_dependencies.packages_name = old.name AND
                package_dependencies.packages_channel = old.channel;
              DELETE FROM package_dependencies_exclude
              WHERE
                package_dependencies_exclude.packages_name = old.name AND
                package_dependencies_exclude.packages_channel = old.channel;
            END;
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
            CREATE TRIGGER channel_check BEFORE DELETE ON channels
            BEGIN
             SELECT RAISE(ROLLBACK, \'Cannot delete channel, installed packages use it\')
             WHERE old.channel IN (SELECT channel FROM packages);
            END;';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TRIGGER channel_delete DELETE ON channels
            FOR EACH ROW BEGIN
              DELETE FROM channel_servers
              WHERE
                channel_servers.channel = old.channel;
              DELETE FROM channel_server_rest
              WHERE
                channel_server_rest.channel = old.channel;
            END;
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE VIEW deps AS
            SELECT
                packages_name,
                packages_channel
                deppackage,
                depchannel,
                null as exclude,
                conflicts,
                min,
                max
            FROM package_dependencies
            UNION
            SELECT
                packages_name,
                packages_channel
                deppackage,
                depchannel,
                exclude,
                conflicts,
                null as min,
                null as max
            FROM package_dependencies_exclude
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE VIEW protocols AS
            SELECT
                channel,
                server,
                baseurl as function,
                type as version,
                "rest" as protocol
            FROM channel_server_rest
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $worked = @$database->queryExec('COMMIT');
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
    }
}
