<?php
/**
 * \Pyrus\ChannelRegistry\Sqlite3
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * An implementation of a Pyrus channel registry using Sqlite3 as the storage
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\ChannelRegistry;
use \Pyrus\Main as Main, \Pyrus\Registry as Registry, \Pyrus\Registry\Sqlite3 as Db;
class Sqlite3 extends \Pyrus\ChannelRegistry\Base
{
    /**
     * Initialize the registry
     *
     * @param unknown_type $path
     */
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        $this->_path = Db::initRegistry($path, $readonly);
        $this->path = dirname($this->_path);
    }

    protected function lazyInit()
    {
        // lazy initialization
        if (!$this->initialized) {
            $this->_init($this->_path, $this->readonly);
        }

        return parent::lazyInit();
    }

    private function _init($path, $readonly)
    {    
        $this->initialized = true;
        $database = Db::getRegistry($path);
        
        $sql = 'SELECT COUNT(*) FROM channels';
        if (!$database->querySingle($sql)) {
            if ($readonly) {
                throw new Registry\Exception('Cannot create SQLite3 channel registry, registry is read-only');
            }
            $this->initDefaultChannels();
        }
    }

    function exists($channel, $strict = true)
    {
        if (!$this->initialized) {
            $this->lazyInit();
        }
        $database = Db::getRegistry($this->_path);

        $sql = 'SELECT channel FROM channels WHERE alias = "' . $database->escapeString($channel) . '"';
        if (!$strict && $a = $database->querySingle($sql)) {
            return true;
        }

        $sql = 'SELECT channel FROM channels WHERE channel = "' . $database->escapeString($channel) . '"';
        if ($a = $database->querySingle($sql)) {
            return true;
        }

        return parent::exists($channel, $strict);
    }

    function add(\Pyrus\ChannelInterface $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new Exception('Cannot add channel, registry is read-only');
        }

        $this->lazyInit();
        $database = Db::getRegistry($this->_path);

        $sql = 'SELECT channel FROM channels WHERE channel = "' . $database->escapeString($channel->name) . '"';
        if ($database->querySingle($sql)) {
            if (!$update) {
                throw new Exception('Error: channel ' . $channel->name . ' has already been discovered');
            }
            $database->exec('BEGIN');
            $database->exec('DELETE FROM channel_servers
              WHERE
                channel = "'
                . $database->escapeString($channel->name) . '";
              DELETE FROM channel_server_rest
              WHERE
                channel = "' . $database->escapeString($channel->name) . '"');
        } elseif ($update) {
            throw new Exception('Error: channel ' . $channel->name . ' is unknown');
        } else {
            $database->exec('BEGIN');
        }

        $database->enableExceptions(true);
        try {
            $this->_add($channel, $lastmodified, $update);
        } catch (\Exception $e) {
            $database->enableExceptions(false);
            @$database->exec('ROLLBACK');
            throw new Registry\Exception('Error: channel ' . $channel->name .
                ' could not be added to the SQLite3 registry', $e);
        }
        $database->enableExceptions(false);
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
        
        $database = Db::getRegistry($this->_path);

        $stmt = $database->prepare($sql);

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
        $stmt = $database->prepare($sql);

        $stmt->bindValue(':channel', $cn);
        $stmt->bindValue(':server',  $cn);
        $stmt->bindValue(':ssl',     $channel->ssl, SQLITE3_INTEGER);
        $stmt->bindValue(':port',    $channel->port, SQLITE3_INTEGER);

        $stmt->execute();
        $stmt->close();

        if ($channel->name == '__uri') {
            // __uri pseudo-channel has no protocols or mirrors
            $database->exec('COMMIT');
            return;
        }

        foreach ($channel->protocols->rest as $type => $baseurl) {
            $sql = '
                INSERT INTO channel_server_rest
                (channel, server, baseurl, type)
                VALUES(
                    :channel, :server, :func, :attrib
                )';

            $stmt = $database->prepare($sql);

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
        $stmt = $database->prepare($sql);
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

            $stmt1 = $database->prepare($sql);
            foreach ($mirror->protocols->rest as $type => $baseurl) {

                $stmt1->bindValue(':channel', $cn);
                $stmt1->bindValue(':server',  $mn);
                $stmt1->bindValue(':func',    $baseurl);
                $stmt1->bindValue(':attrib',  $type);

                $stmt1->execute();
            }
        }
        $database->exec('COMMIT');
    }

    function update(\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot update channel, registry is read-only');
        }

        return $this->add($channel, true);
    }

    function get($channel, $strict = true)
    {
        $exists = $this->exists($channel, $strict);
        if (!$exists) {
            throw new Exception('Unknown channel: ' . $channel);
        }

        $chan = $this->getChannelObject($this->channelFromAlias($channel));
        return new Channel($this, $chan->getArray());
    }

    function channelFromAlias($alias)
    {
        if (!$this->initialized) {
            $this->lazyInit();
        }
        $database = Db::getRegistry($this->_path);
        
        $sql = 'SELECT channel FROM channels WHERE alias = "' .
            $database->escapeString($alias) . '"';
        if ($chan = $database->querySingle($sql)) {
            return $chan;
        }
        $sql = 'SELECT channel FROM channels WHERE channel = "' .
            $database->escapeString($alias) . '"';
        if ($chan = $database->querySingle($sql)) {
            return $chan;
        }
        throw new \Pyrus\ChannelFile\Exception('Unknown channel/alias: ' . $alias);
    }

    /**
     * @param string
     */
    protected function getChannelObject($channel)
    {
        $channel = $this->channelFromAlias($channel);
        if (!$this->initialized) {
            $this->lazyInit();
        }
        $database = Db::getRegistry($this->_path);
        
        $sql = 'SELECT * FROM channels WHERE channel = "' .
            $database->escapeString($channel) . '"';

        $result = $database->query($sql);
        if (!$result) {
            throw new \Pyrus\ChannelFile\Exception('Failed to query channels table');
        }

        while ($res = $result->fetchArray(SQLITE3_ASSOC)) {
            break;
        }
        $ret = new \Pyrus\ChannelFile\v1;
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
            $database->escapeString($channel) . '"';
        $result = $database->query($sql);
        if (!$result) {
            throw new \Pyrus\ChannelFile\Exception('Failed to query mirrors table');
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
            $database->escapeString($channel) . '"';
        $result = $database->query($sql);
        if (!$result) {
            throw new \Pyrus\ChannelFile\Exception('Failed to query rest table');
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

    function delete(\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot delete channel, registry is read-only');
        }

        $name = $channel->name;
        if (in_array($name, $this->getDefaultChannels())) {
            throw new Exception('Cannot delete default channel ' . $channel->name);
        }

        $this->lazyInit();
        $database = Db::getRegistry($this->_path);

        $sql = 'SELECT count(*) FROM packages WHERE channel = "' .
            $database->escapeString($channel->name) . '"';
        if ($database->querySingle($sql)) {
            throw new \Pyrus\ChannelRegistry\Exception('Cannot delete channel ' .
                $channel->name . ', packages are installed');
        }


        $sql = 'DELETE FROM channels WHERE channel = "' .
            $database->escapeString($channel->name) . '"';
        $database->enableExceptions(true);
        try {
            $database->exec($sql);
        } catch (\Exception $e) {
            $database->enableExceptions(false);
            throw new Registry\Exception('Error: Deleting channel ' .
                $channel->name . ' failed: ' . $e->getMessage(), $e);
        }
        $database->enableExceptions(false);
    }

    public function listChannels()
    {
        if (!$this->initialized) {
            $this->lazyInit();
        }
        $database = Db::getRegistry($this->_path);

        $ret = array();
        $sql = 'SELECT channel FROM channels ORDER BY channel';
        $res = $database->query($sql);
        while ($chan = $res->fetchArray(SQLITE3_ASSOC)) {
            $ret[] = $chan['channel'];
        }

        return $ret;
    }
}
