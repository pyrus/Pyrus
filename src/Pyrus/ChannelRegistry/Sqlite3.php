<?php
/**
 * \pear2\Pyrus\ChannelRegistry\Sqlite3
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
namespace pear2\Pyrus\ChannelRegistry;
class Sqlite3 extends \pear2\Pyrus\ChannelRegistry\Base
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
        $this->path = $path;
        if ($path && $path != ':memory:') {
            if (dirname($path . '.pear2registry') != $path) {
                $path = $path . DIRECTORY_SEPARATOR . '.pear2registry';
            } else {
                $this->path = dirname($path);
            }
        }
        if ($path != ':memory:') {
            if (file_exists($path)) {
                $this->_init($path, $readonly);
            } else {
                $file = $path;
                while ($file && $file !== '.' && $file !== '/' && !is_writable($file)) {
                    $file = dirname($file);
                }
                if (!$file || $file == '.') {
                    throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot create SQLite3 channel registry, registry is read-only');
                }
            }
        }
    }

    protected function lazyInit()
    {
        // lazy initialization
        if (!$this->initialized) {
            $this->_init($this->path . '/.pear2registry', $this->readonly);
        }

        return parent::lazyInit();
    }

    private function _init($path, $readonly)
    {
        if (isset(static::$databases[$this->path]) && static::$databases[$this->path]) {
            $this->initialized = true;
            return;
        }

        $dbpath = $path;
        if ($path != ':memory:' && isset(\pear2\Pyrus\Main::$options['packagingroot'])) {
            $dbpath = \pear2\Pyrus\Main::prepend(\pear2\Pyrus\Main::$options['packagingroot'], $path);
        }

        if (!$path) {
            $path = ':memory:';
        } elseif ($path != ':memory:' && !file_exists(dirname($dbpath))) {
            if ($readonly) {
                throw new \pear2\Pyrus\Registry\Exception('Cannot create SQLite3 channel registry, registry is read-only');
            }
            @mkdir(dirname($dbpath), 0755, true);
        }

        if ($readonly && $path != ':memory:' && !file_exists(dirname($dbpath))) {
            throw new \pear2\Pyrus\Registry\Exception('Cannot create SQLite3 channel registry, registry is read-only');
        }

        static::$databases[$this->path] = new \SQLite3($dbpath);
        // hopefully this works
        if (static::$databases[$this->path]->lastErrorCode()) {
            $temp = static::$databases[$this->path];
            unset(static::$databases[$this->path]);
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot open SQLite3 channel registry: ' . $temp->lastErrorMsg());
        }
        $this->initialized = true;

        $sql = 'SELECT version FROM pearregistryversion';
        if (@static::$databases[$this->path]->querySingle($sql) == '1.0.0') {
            $sql = 'SELECT COUNT(*) FROM channels';
            if (!static::$databases[$this->path]->querySingle($sql)) {
                if ($readonly) {
                    throw new \pear2\Pyrus\Registry\Exception('Cannot create SQLite3 channel registry, registry is read-only');
                }
                $this->initDefaultChannels();
                return;
            }
            return;
        }

        if ($readonly) {
            throw new \pear2\Pyrus\Registry\Exception('Cannot create SQLite3 channel registry, registry is read-only');
        }

        $a = new \pear2\Pyrus\Registry\Sqlite3\Creator;
        $a->create(static::$databases[$this->path]);
        $this->initDefaultChannels();
    }

    function exists($channel, $strict = true)
    {
        if (!$this->initialized) {
            return parent::exists($channel, $strict);
        }

        $sql = 'SELECT channel FROM channels WHERE alias = "' . static::$databases[$this->path]->escapeString($channel) . '"';
        if (!$strict && $a = static::$databases[$this->path]->querySingle($sql)) {
            return true;
        }

        $sql = 'SELECT channel FROM channels WHERE channel = "' . static::$databases[$this->path]->escapeString($channel) . '"';
        if ($a = static::$databases[$this->path]->querySingle($sql)) {
            return true;
        }

        return parent::exists($channel, $strict);
    }

    function add(\pear2\Pyrus\IChannel $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot add channel, registry is read-only');
        }

        $this->lazyInit();

        $sql = 'SELECT channel FROM channels WHERE channel = "' . static::$databases[$this->path]->escapeString($channel->name) . '"';
        if (static::$databases[$this->path]->querySingle($sql)) {
            if (!$update) {
                throw new \pear2\Pyrus\ChannelRegistry\Exception('Error: channel ' .
                    $channel->name . ' has already been discovered');
            }
            static::$databases[$this->path]->exec('BEGIN');
            static::$databases[$this->path]->exec('DELETE FROM channel_servers
              WHERE
                channel = "'
                . static::$databases[$this->path]->escapeString($channel->name) . '";
              DELETE FROM channel_server_rest
              WHERE
                channel = "' . static::$databases[$this->path]->escapeString($channel->name) . '"');
        } elseif ($update) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Error: channel ' .
                $channel->name . ' is unknown');
        } else {
            static::$databases[$this->path]->exec('BEGIN');
        }

        static::$databases[$this->path]->enableExceptions(true);
        try {
            $this->_add($channel, $lastmodified, $update);
        } catch (\Exception $e) {
            static::$databases[$this->path]->enableExceptions(false);
            @static::$databases[$this->path]->exec('ROLLBACK');
            throw new \pear2\Pyrus\Registry\Exception('Error: channel ' . $channel->name .
                ' could not be added to the SQLite3 registry', $e);
        }
        static::$databases[$this->path]->enableExceptions(false);
    }

    private function _add($channel, $lastmodified, $update)
    {
        $validate = $channel->getValidationPackage();

        if ($update) {
            $sql = '
                UPDATE channels set
                    summary=:summary,
                    suggestedalias=:suggestedalias,
                    alias=:alias,
                    validatepackageversion=:version,
                    validatepackage=:package,
                    lastmodified=:lastmodified
                WHERE
                    channel=:name';
        } else {
            $sql = '
                INSERT INTO channels
                (channel, summary, suggestedalias, alias, validatepackageversion,
                validatepackage, lastmodified)
                VALUES(
                    :name, :summary, :suggestedalias,
                    :alias, :version, :package, :lastmodified
                )';
        }

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

    function update(\pear2\Pyrus\IChannel $channel)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot update channel, registry is read-only');
        }

        return $this->add($channel, true);
    }

    function get($channel, $strict = true)
    {
        $exists = $this->exists($channel, $strict);
        if (!$exists) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Unknown channel: ' . $channel);
        }

        $chan = $this->getChannelObject($this->channelFromAlias($channel));
        return new \pear2\Pyrus\ChannelRegistry\Channel($this, $chan->getArray());
    }

    function channelFromAlias($alias)
    {
        if (!$this->initialized) {
            return parent::channelFromAlias($alias);
        }
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
        throw new \pear2\Pyrus\ChannelFile\Exception('Unknown channel/alias: ' . $alias);
    }

    /**
     * @param string
     */
    protected function getChannelObject($channel)
    {
        $channel = $this->channelFromAlias($channel);
        if (!$this->initialized) {
            if (in_array($channel, $this->getDefaultChannels())) {
                return $this->getDefaultChannel($channel);
            }
            throw new \pear2\Pyrus\ChannelFile\Exception('Unknown channel ' . $channel);
        }
        $sql = 'SELECT * FROM channels WHERE channel = "' .
            static::$databases[$this->path]->escapeString($channel) . '"';

        $result = static::$databases[$this->path]->query($sql);
        if (!$result) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Failed to query channels table');
        }

        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            break;
        }
        $ret = new \pear2\Pyrus\ChannelFile\v1;
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
            throw new \pear2\Pyrus\ChannelFile\Exception('Failed to query mirrors table');
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
            throw new \pear2\Pyrus\ChannelFile\Exception('Failed to query rest table');
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

    function delete(\pear2\Pyrus\IChannel $channel)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot delete channel, registry is read-only');
        }

        $name = $channel->name;
        if (in_array($name, $this->getDefaultChannels())) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot delete default channel ' .
                $channel->name);
        }

        $this->lazyInit();

        if (!isset(static::$databases[$this->path])) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Error: no existing SQLite3 channel registry for ' . $this->path);
        }

        $sql = 'SELECT count(*) FROM packages WHERE channel = "' .
            static::$databases[$this->path]->escapeString($channel->name) . '"';
        if (static::$databases[$this->path]->querySingle($sql)) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot delete channel ' .
                $channel->name . ', packages are installed');
        }


        $sql = 'DELETE FROM channels WHERE channel = "' .
            static::$databases[$this->path]->escapeString($channel->name) . '"';
        static::$databases[$this->path]->enableExceptions(true);
        try {
            static::$databases[$this->path]->exec($sql);
        } catch (\Exception $e) {
            static::$databases[$this->path]->enableExceptions(false);
            throw new \pear2\Pyrus\Registry\Exception('Error: Deleting channel ' .
                $channel->name . ' failed: ' . $e->getMessage(), $e);
        }
        static::$databases[$this->path]->enableExceptions(false);
    }

    public function listChannels()
    {
        if (!$this->initialized) {
            return $this->getDefaultChannels();
        }
        if (!isset(static::$databases[$this->path])) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception(
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
