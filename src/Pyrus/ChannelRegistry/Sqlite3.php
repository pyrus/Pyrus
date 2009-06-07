<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Sqlite3
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
 * An implementation of a Pyrus channel registry using Sqlite3 as the storage
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelRegistry_Sqlite3 extends PEAR2_Pyrus_ChannelRegistry_Base
{
    /**
     * The database resource
     *
     * @var SQLite3
     */
    static protected $databases = array();

    /**
     * Initialize the registry
     *
     * @param unknown_type $path
     */
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        if ($path && $path != ':memory:') {
            if (dirname($path . '.pear2registry') != $path) {
                $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
            }
        }
        $this->path = $path;
        $this->_init($path, $readonly);
    }

    private function _init($path, $readonly)
    {
        if (isset(static::$databases[$path]) && static::$databases[$path]) {
            return;
        }

        $dbpath = $path;
        if ($path != ':memory:' && isset(PEAR2_Pyrus::$options['packagingroot'])) {
            $dbpath = PEAR2_Pyrus::prepend(PEAR2_Pyrus::$options['packagingroot'], $path);
        }

        if (!$path) {
            $path = ':memory:';
        } elseif ($path != ':memory:' && !file_exists(dirname($dbpath))) {
            if ($readonly) {
                throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
            }
            @mkdir(dirname($dbpath), 0755, true);
        }

        if ($readonly && $path != ':memory:' && !file_exists($dbpath)) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
        }

        static::$databases[$path] = new SQLite3($dbpath);
        // hopefully this works
        if (static::$databases[$path]->lastErrorCode()) {
            $temp = static::$databases[$path];
            unset(static::$databases[$path]);
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot open SQLite3 channel registry: ' . $temp->lastErrorMsg());
        }

        $sql = 'SELECT version FROM pearregistryversion';
        if (@static::$databases[$path]->querySingle($sql) == '1.0.0') {
            $sql = 'SELECT COUNT(*) FROM channels';
            if (!static::$databases[$path]->querySingle($sql)) {
                if ($readonly) {
                    throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
                }
                $this->initDefaultChannels();
                return;
            }
            return;
        }

        if ($readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot create SQLite3 channel registry, registry is read-only');
        }

        $a = new PEAR2_Pyrus_Registry_Sqlite3_Creator;
        $a->create(static::$databases[$path]);
        $this->initDefaultChannels();
    }

    function exists($channel, $strict = true)
    {
        if (!isset(static::$databases[$this->path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->path);
        }

        $sql = 'SELECT channel FROM channels WHERE alias = "' . static::$databases[$this->path]->escapeString($channel) . '"';
        if (!$strict && $a = static::$databases[$this->path]->querySingle($sql)) {
            return true;
        }

        $sql = 'SELECT channel FROM channels WHERE channel = "' . static::$databases[$this->path]->escapeString($channel) . '"';
        if ($a = static::$databases[$this->path]->querySingle($sql)) {
            return true;
        }

        return false;
    }

    function add(PEAR2_Pyrus_IChannel $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add channel, registry is read-only');
        }

        if (!isset(static::$databases[$this->path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->path);
        }

        $sql = 'SELECT channel FROM channels WHERE channel = "' . static::$databases[$this->path]->escapeString($channel->name) . '"';
        if (static::$databases[$this->path]->querySingle($sql)) {
            if (!$update) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                    $channel->name . ' has already been discovered');
            }
            static::$databases[$this->path]->exec('BEGIN');
            $this->delete($channel, true);
        } elseif ($update) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->name . ' is unknown');
        } else {
            static::$databases[$this->path]->exec('BEGIN');
        }

        static::$databases[$this->path]->enableExceptions(true);
        try {
            $this->_add($channel, $lastmodified);
        } catch (\Exception $e) {
            static::$databases[$this->path]->enableExceptions(false);
            @static::$databases[$this->path]->exec('ROLLBACK');
            throw new PEAR2_Pyrus_Registry_Exception('Error: channel ' . $channel->name .
                ' could not be added to the SQLite3 registry', $e);
        }
        static::$databases[$this->path]->enableExceptions(false);
    }

    private function _add($channel, $lastmodified)
    {
        $validate = $channel->getValidationPackage();

        $sql = '
            INSERT INTO channels
            (channel, summary, suggestedalias, alias, validatepackageversion,
            validatepackage, lastmodified)
            VALUES(
                :name, :summary, :suggestedalias,
                :alias, :version, :package, :lastmodified
            )';

        $stmt = static::$databases[$this->path]->prepare($sql);

        $stmt->bindValue(':name',           $cn = $channel->name);
        $stmt->bindValue(':summary',        $channel->summary);
        $stmt->bindValue(':suggestedalias', $channel->suggestedalias);
        $stmt->bindValue(':alias',          $channel->alias);
        $stmt->bindValue(':version',        $validate['attribs']['version']);
        $stmt->bindValue(':package',        $validate['_content']);
        $stmt->bindValue(':lastmodified',   $channel->lastModified());

        $stmt->execute();
        $stmt->close();

        $sql = '
            INSERT INTO channel_servers
            (channel, server, ssl, port)
            VALUES(
                :channel, :server, :ssl, :port
            )';
        $stmt = static::$databases[$this->path]->prepare($sql);

        $stmt->bindValue(':channel', $cn);
        $stmt->bindValue(':server',  $cn);
        $stmt->bindValue(':ssl',     $channel->ssl, SQLITE3_INTEGER);
        $stmt->bindValue(':port',    $channel->port, SQLITE3_INTEGER);

        $stmt->execute();
        $stmt->close();

        if ($channel->name == '__uri') {
            // __uri pseudo-channel has no protocols or mirrors
            static::$databases[$this->path]->exec('COMMIT');
            return;
        }

        foreach ($channel->protocols->rest as $type => $baseurl) {
            $sql = '
                INSERT INTO channel_server_rest
                (channel, server, baseurl, type)
                VALUES(
                    :channel, :server, :func, :attrib
                )';

            $stmt = static::$databases[$this->path]->prepare($sql);

            $stmt->bindValue(':channel', $cn);
            $stmt->bindValue(':server',  $cn);
            $stmt->bindValue(':func',    $baseurl);
            $stmt->bindValue(':attrib',  $type);

            $stmt->execute();
        }

        $sql = '
            INSERT INTO channel_servers
            (channel, server, ssl, port)
            VALUES(
                :channel, :server, :ssl, :port
            )';
        $stmt = static::$databases[$this->path]->prepare($sql);
        foreach ($channel->mirrors as $mirror) {

            $ssl = 0;
            if ($mirror->ssl) {
                $ssl = 1;
            }

            $stmt->bindValue(':channel', $cn);
            $stmt->bindValue(':server',  $mn = $mirror->name);
            $stmt->bindValue(':ssl',     $ssl, SQLITE3_INTEGER);
            $stmt->bindValue(':port',    $mirror->port, SQLITE3_INTEGER);

            $stmt->execute();

            $sql = '
                INSERT INTO channel_server_rest
                (channel, server, baseurl, type)
                VALUES(
                    :channel, :server, :func, :attrib
                )';

            $stmt1 = static::$databases[$this->path]->prepare($sql);
            foreach ($mirror->protocols->rest as $type => $baseurl) {

                $stmt1->bindValue(':channel', $cn);
                $stmt1->bindValue(':server',  $mn);
                $stmt1->bindValue(':func',    $baseurl);
                $stmt1->bindValue(':attrib',  $type);

                $stmt1->execute();
            }
        }
        static::$databases[$this->path]->exec('COMMIT');
    }

    function update(PEAR2_Pyrus_IChannel $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot update channel, registry is read-only');
        }

        return $this->add($channel, true);
    }

    function get($channel, $strict = true)
    {
        if (!isset(static::$databases[$this->path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->path);
        }

        if (!$this->exists($channel, $strict)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
        }

        $chan = $this->getChannelObject($this->channelFromAlias($channel));
        return new PEAR2_Pyrus_ChannelRegistry_Channel($this, $chan->getArray());
    }

    function channelFromAlias($alias)
    {
        $sql = 'SELECT channel FROM channels WHERE alias = "' .
            static::$databases[$this->path]->escapeString($alias) . '"';
        if ($chan = static::$databases[$this->path]->querySingle($sql)) {
            return $chan;
        }
        $sql = 'SELECT channel FROM channels WHERE channel = "' .
            static::$databases[$this->path]->escapeString($alias) . '"';
        if ($chan = static::$databases[$this->path]->querySingle($sql)) {
            return $chan;
        }
        throw new PEAR2_Pyrus_ChannelFile_Exception('Unknown channel/alias: ' . $alias);
    }

    /**
     * @param string
     */
    protected function getChannelObject($channel)
    {
        $channel = $this->channelFromAlias($channel);
        $sql = 'SELECT * FROM channels WHERE channel = "' .
            static::$databases[$this->path]->escapeString($channel) . '"';

        $result = static::$databases[$this->path]->query($sql);
        if (!$result) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Failed to query channels table');
        }

        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            break;
        }
        $ret = new PEAR2_Pyrus_ChannelFile_v1;
        $ret->name = $channel;
        $ret->suggestedalias = $res['suggestedalias'];
        if ($res['alias']) {
            $ret->alias = $res['alias'];
        }
        $ret->summary = $res['summary'];
        $ret->setValidationPackage($res['validatepackage'], $res['validatepackageversion']);
        $ret->lastModified = $res['lastmodified'];

        $sql = 'SELECT channel, server, ssl, port FROM channel_servers
            WHERE channel = "' .
            static::$databases[$this->path]->escapeString($channel) . '"';
        $result = static::$databases[$this->path]->query($sql);
        if (!$result) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Failed to query mirrors table');
        }

        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($res['server'] == $res['channel']) {
                $ret->port = $res['port'];
                $ret->ssl = $res['ssl'];
            } else {
                $ret->mirrors[$res['server']]->ssl = $res['ssl'];
                $ret->mirrors[$res['server']]->port = $res['port'];
            }
        }

        $sql = 'SELECT channel, server, baseurl, type FROM channel_server_rest
            WHERE channel= "' .
            static::$databases[$this->path]->escapeString($channel) . '"';
        $result = static::$databases[$this->path]->query($sql);
        if (!$result) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Failed to query rest table');
        }

        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            if ($res['server'] == $res['channel']) {
                $ret->protocols->rest[$res['type']]->baseurl = $res['baseurl'];
            } else {
                $ret->mirrors[$res['server']]->protocols->rest[$res['type']]->baseurl = $res['baseurl'];
            }
        }
        return $ret;
    }

    function delete(PEAR2_Pyrus_IChannel $channel, $inupdate = false)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete channel, registry is read-only');
        }

        if (!isset(static::$databases[$this->path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: no existing SQLite3 channel registry for ' . $this->path);
        }

        $name = $channel->name;
        if (in_array($name, $this->getDefaultChannels())) {
            if (!$inupdate) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete default channel ' .
                    $channel->name);
            }
        }

        $sql = 'SELECT count(*) FROM packages WHERE channel = "' .
            static::$databases[$this->path]->escapeString($channel->name) . '"';
        if (static::$databases[$this->path]->querySingle($sql)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete channel ' .
                $channel->name . ', packages are installed');
        }


        $sql = 'DELETE FROM channels WHERE channel = "' .
            static::$databases[$this->path]->escapeString($channel->name) . '"';
        static::$databases[$this->path]->enableExceptions(true);
        try {
            static::$databases[$this->path]->exec($sql);
        } catch (\Exception $e) {
            static::$databases[$this->path]->enableExceptions(false);
            throw new PEAR2_Pyrus_Registry_Exception('Error: Deleting channel ' .
                $channel->name . ' failed: ' . $e->getMessage(), $e);
        }
        static::$databases[$this->path]->enableExceptions(false);
    }

    public function listChannels()
    {
        if (!isset(static::$databases[$this->path])) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception(
                'Error: no existing SQLite3 channel registry for ' . $this->path);
        }

        $ret = array();
        $sql = 'SELECT channel FROM channels ORDER BY channel';
        $res = static::$databases[$this->path]->query($sql);
        while ($chan = $res->fetchArray(SQLITE_ASSOC)) {
            $ret[] = $chan['channel'];
        }

        return $ret;
    }
}
