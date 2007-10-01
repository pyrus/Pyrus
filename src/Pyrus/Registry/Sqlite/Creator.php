<?php
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
     *  name VARCHAR(80) NOT NULL,
     *  channel VARCHAR(255) NOT NULL,
     *  version VARCHAR(20) NOT NULL,
     *  apiversion VARCHAR(20) NOT NULL,
     *  summary TEXT NOT NULL,
     *  description TEXT NOT NULL,
     *  stability VARCHAR(8) NOT NULL,
     *  apistability VARCHAR(8) NOT NULL,
     *  releasedate DATE NOT NULL,
     *  releasetime TIME,
     *  license VARCHAR(50) NOT NULL,
     *  licenseuri TEXT,
     *  licensepath TEXT,
     *  releasenotes TEXT,
     *  lastinstalledversion VARCHAR(20),
     *  installedwithpear VARCHAR(20),
     *  installtimeconfig VARCHAR(50), -- the path to configuration as stored
     *  PRIMARY KEY (name, channel)
     * );
     * 
     * CREATE TABLE maintainers (
     *  packages_name VARCHAR(80) NOT NULL,
     *  packages_channel VARCHAR(255) NOT NULL,
     *  role VARCHAR(11) NOT NULL,
     *  user VARCHAR(20) NOT NULL,
     *  email VARCHAR(100) NOT NULL,
     *  active CHAR(3) NOT NULL,
     *  PRIMARY KEY (packages_name, packages_channel, user)
     * );
     * 
     * CREATE TABLE files (
     *  packages_name VARCHAR(80) NOT NULL,
     *  packages_channel VARCHAR(255) NOT NULL,
     *  packagepath VARCHAR(255) NOT NULL,
     *  role VARCHAR(30) NOT NULL,
     *  rolepath VARCHAR(255) NOT NULL,
     *  PRIMARY KEY (packagepath, role, rolepath),
     *  UNIQUE (packages_name, packages_channel, packagepath)
     * );
     *
     * CREATE TABLE package_dependencies (
     *  required BOOL NOT NULL,
     *  packages_name VARCHAR(80) NOT NULL,
     *  packages_channel VARCHAR(255) NOT NULL,
     *  deppackage VARCHAR(80) NOT NULL,
     *  depchannel VARCHAR(255) NOT NULL,
     *  conflicts BOOL NOT NULL,
     *  min VARCHAR(20),
     *  max VARCHAR(20),
     *  PRIMARY KEY (required, packages_name, packages_channel, deppackage, depchannel)
     * );
     *
     * CREATE TABLE package_dependencies_exclude (
     *  required BOOL NOT NULL,
     *  packages_name VARCHAR(80) NOT NULL,
     *  packages_channel VARCHAR(255) NOT NULL,
     *  deppackage VARCHAR(80) NOT NULL,
     *  depchannel VARCHAR(255) NOT NULL,
     *  conflicts BOOL NOT NULL,
     *  exclude VARCHAR(20),
     *  PRIMARY KEY (required, packages_name, packages_channel, deppackage, depchannel)
     * );
     * 
     * CREATE TABLE channels (
     *  channel TEXT NOT NULL,
     *  summary TEXT NOT NULL,
     *  suggestedalias VARCHAR(50) NOT NULL,
     *  alias VARCHAR(50) NOT NULL,
     *  validatepackageversion VARCHAR(20) NOT NULL default "default",
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
     *  xmlrpcpath TEXT NOT NULL,
     *  soappath TEXT NOT NULL,
     *  PRIMARY KEY (channel, server)
     * );
     * 
     * CREATE TABLE channel_server_xmlrpc (
     *  channel TEXT NOT NULL,
     *  server TEXT NOT NULL,
     *  function TEXT NOT NULL,
     *  version VARCHAR(20) NOT NULL,
     *  PRIMARY KEY (channel, server, function, version)
     * );
     * 
     * CREATE TABLE channel_server_soap (
     *  channel TEXT NOT NULL,
     *  server TEXT NOT NULL,
     *  function TEXT NOT NULL,
     *  version VARCHAR(20) NOT NULL,
     *  PRIMARY KEY (channel, server, function, version)
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
     *  version VARCHAR(20) NOT NULL default "1.0.0"
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
     *     DELETE FROM channel_server_xmlrpc
     *     WHERE
     *       channel_server_xmlrpc.channel = old.channel;
     *     DELETE FROM channel_server_soap
     *     WHERE
     *       channel_server_soap.channel = old.channel;
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
     *      function,
     *      version,
     *      "xmlrpc" as protocol
     *  FROM channel_server_xmlrpc
     *  UNION
     *  SELECT
     *      channel,
     *      server,
     *      function,
     *      version,
     *      "soap" as protocol
     *  FROM channel_server_soap
     *  UNION
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
           name VARCHAR(80) NOT NULL,
           channel VARCHAR(255) NOT NULL,
           version VARCHAR(20) NOT NULL,
           apiversion VARCHAR(20) NOT NULL,
           summary TEXT NOT NULL,
           description TEXT NOT NULL,
           stability VARCHAR(8) NOT NULL,
           apistability VARCHAR(8) NOT NULL,
           releasedate DATE NOT NULL,
           releasetime TIME,
           license VARCHAR(50) NOT NULL,
           licenseuri TEXT,
           licensepath TEXT,
           releasenotes TEXT,
           lastinstalledversion VARCHAR(20),
           installedwithpear VARCHAR(20),
           installtimeconfig VARCHAR(50),
           PRIMARY KEY (name, channel)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
          
        $query = '
          CREATE TABLE maintainers (
           packages_name VARCHAR(80) NOT NULL,
           packages_channel VARCHAR(255) NOT NULL,
           role VARCHAR(11) NOT NULL,
           user VARCHAR(20) NOT NULL,
           email VARCHAR(100) NOT NULL,
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
           packages_name VARCHAR(80) NOT NULL,
           packages_channel VARCHAR(255) NOT NULL,
           packagepath VARCHAR(255) NOT NULL,
           role VARCHAR(30) NOT NULL,
           rolepath VARCHAR(255) NOT NULL,
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
           packages_name VARCHAR(80) NOT NULL,
           packages_channel VARCHAR(255) NOT NULL,
           deppackage VARCHAR(80) NOT NULL,
           depchannel VARCHAR(255) NOT NULL,
           conflicts BOOL NOT NULL,
           min VARCHAR(20),
           max VARCHAR(20),
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
           packages_name VARCHAR(80) NOT NULL,
           packages_channel VARCHAR(255) NOT NULL,
           deppackage VARCHAR(80) NOT NULL,
           depchannel VARCHAR(255) NOT NULL,
           exclude VARCHAR(20),
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
           suggestedalias VARCHAR(50) NOT NULL,
           alias VARCHAR(50) NOT NULL,
           validatepackageversion VARCHAR(20) NOT NULL default "default",
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
           xmlrpcpath TEXT NOT NULL,
           soappath TEXT NOT NULL,
           PRIMARY KEY (channel, server)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE channel_server_xmlrpc (
           channel TEXT NOT NULL,
           server TEXT NOT NULL,
           function TEXT NOT NULL,
           version VARCHAR(20) NOT NULL,
           PRIMARY KEY (channel, server, function, version)
          );';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }

        $query = '
          CREATE TABLE channel_server_soap (
           channel TEXT NOT NULL,
           server TEXT NOT NULL,
           function TEXT NOT NULL,
           version VARCHAR(20) NOT NULL,
           PRIMARY KEY (channel, server, function, version)
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
           version VARCHAR(20) NOT NULL
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
              DELETE FROM channel_server_xmlrpc
              WHERE
                channel_server_xmlrpc.channel = old.channel;
              DELETE FROM channel_server_soap
              WHERE
                channel_server_soap.channel = old.channel;
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
                function,
                version,
                "xmlrpc" as protocol
            FROM channel_server_xmlrpc
            UNION
            SELECT
                channel,
                server,
                function,
                version,
                "soap" as protocol
            FROM channel_server_soap
            UNION
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
        $query = '
            INSERT INTO channels
             (channel, summary, suggestedalias, alias, lastmodified)
            VALUES(
             "pear2.php.net",
             "PHP Extension and Application Repository",
             "pear2",
             "pear2",
             datetime("now")
            )
        ';
        $query = '
            INSERT INTO channels
             (channel, summary, suggestedalias, alias, lastmodified)
            VALUES(
             "pear.php.net",
             "PHP Extension and Application Repository",
             "pear",
             "pear",
             datetime("now")
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
            VALUES(
             "pear.php.net",
             "pear.php.net",
             "REST1.0",
             "http://pear.php.net/rest/"
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
            VALUES(
             "pear.php.net",
             "pear.php.net",
             "REST1.1",
             "http://pear.php.net/rest/"
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
            VALUES(
             "pear.php.net",
             "pear.php.net",
             "REST1.2",
             "http://pear.php.net/rest/"
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
            VALUES(
             "pear.php.net",
             "pear.php.net",
             "REST1.3",
             "http://pear.php.net/rest/"
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channels
             (channel, summary, suggestedalias, alias, validatepackageversion,
              validatepackage, lastmodified)
            VALUES(
             "pecl.php.net",
             "PHP Extension and Community Library",
             "pecl",
             "pecl",
             "default",
             "PEAR_Validate_PECL",
             datetime("now")
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
            VALUES(
             "pecl.php.net",
             "pecl.php.net",
             "REST1.0",
             "http://pecl.php.net/rest/"
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
            VALUES(
             "pecl.php.net",
             "pecl.php.net",
             "REST1.1",
             "http://pecl.php.net/rest/"
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        $query = '
            INSERT INTO channels
             (channel, summary, suggestedalias, alias, lastmodified)
            VALUES(
             "__uri",
             "pseudo-channel for static packages",
             "__uri",
             "__uri",
             datetime("now")
            )
        ';
        $worked = @$database->queryExec($query, $error);
        if (!$worked) {
            @$database->queryExec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Cannot initialize SQLite registry: ' . $error);
        }
        @$database->queryExec('COMMIT');
    }
}