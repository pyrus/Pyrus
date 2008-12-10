<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite
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
 * A class for handling a channel entry within an Sqlite channel registry.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite3 implements PEAR2_Pyrus_IChannel, Countable
{
    /**
     * The database resource
     *
     * @var SQLiteDatabase
     */
    protected $database;
    protected $mirror;
    private $_path;
    protected $channelname;

    function __construct(SQLite3 $db, $channel)
    {
        $channel = strtolower($channel);
        $this->database = $db;
        $this->channelname = $this->mirror = $channel;
        $sql = 'SELECT channel FROM channels WHERE channel = "' . $this->database->escapeString($channel) . '"';
        if (!$this->database->querySingle($sql)) {
            $sql = 'SELECT channel FROM channels WHERE alias = "' . $this->database->escapeString($channel) . '"';
            if (!($channel = $this->database->querySingle($sql))) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Channel ' .
                    $this->channelname . ' does not exist');
            }
            $this->channelname = $channel;
        }
    }

    function count()
    {
        $sql = 'SELECT COUNT(*) FROM packages WHERE channel = "' . $this->database->escapeString($this->channelname) . '"';
        return $this->database->querySingle($sql);
    }

    function getName()
    {
        return $this->channelname;
    }

    function setAlias($alias)
    {
        $error = '';
        $sql   = 'UPDATE channels SET alias = \'' . $this->database->escapeString($alias) . '\'';
        if (!@$this->database->exec($sql, $error)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot set channel ' .
                $this->channelname . ' alias to ' . $alias . ': ' . $error);
        }
    }

    function getSummary()
    {
        $sql = 'SELECT summary FROM channels WHERE channel = \'' . $this->database->escapeString($this->channelname) . '\'';
        return $this->database->querySingle($sql);
    }

    /**
     * @return int|80 port number to connect to
     */
    function getPort()
    {
        $sql = 'SELECT port FROM channel_servers WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server = \'' . $this->database->escapeString($this->mirror) . '\'';
        return $this->database->querySingle($sql);
    }

    function getSSL($mirror = false)
    {
        $sql = 'SELECT ssl FROM channel_servers WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server = \'' . $this->database->escapeString($this->mirror) . '\'';
        return $this->database->querySingle($sql);
    }


    function getValidationPackage()
    {
        $sql = 'SELECT validatepackage ' .
              'FROM channels WHERE ' .
              'channel = \'' . $this->database->escapeString($this->channelname) . '\'';
        $r = $this->database->querySingle($sql);
            if ($r == 'PEAR_Validate' || $r == 'PEAR_Validate_PECL') {
                return array('attribs' => array('version' => '1.0'), '_content' => str_replace('PEAR_', 'PEAR2_Pyrus_', $r));
            }

            $sql = 'SELECT validatepackageversion ' .
              'FROM channels WHERE ' .
              'channel = \'' . $this->database->escapeString($this->channelname) . '\'';
            $v = $this->database->querySingle($sql);
            return array('attribs' => array('version' => $v), '_content' => $r);
    }

    public function getREST()
    {
        $sql = 'SELECT * FROM channel_server_rest WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server = \'' . $this->database->escapeString($this->mirror) . '\'';
        $res = $this->database->query($sql);

        $ret = array();
        foreach ($res->arrayFetch(SQLITE_ASSOC) as $url) {
            $ret[] = array('attribs' => array('type' => $url['type']), '_content' => $url['baseurl']);
        }
    }

    public function getValidationObject($package = false)
    {
        $a = $this->getValidationPackage($package);
        $b = $a['_content'];
        return new $b;
    }

    function __get($value)
    {
        switch ($value) {
            case 'mirrors' :
                $ret = array($this->channelname => $this);
                $sql = 'SELECT server FROM channel_servers
                      WHERE channel = \'' . $this->database->escapeString($this->channelname) . '\'
                      AND server != \'' . $this->database->escapeString($this->channelname) . '\'';
                $res = $this->database->query($sql);

                foreach ($res->arrayFetch(SQLITE_ASSOC) as $mirror) {
                    $ret[$mirror['server']] = new PEAR2_Pyrus_ChannelRegistry_Mirror_Sqlite($this->database, $mirror['server'], $this);
                }
            return $ret;
        }

        if (method_exists($this, "get$value")) {
            $gv = "get$value";
            return $this->$gv();
        }
    }

    function __set($var, $value)
    {
        if (method_exists($this, "set$var")) {
            $sv = "set$var";
            $this->$sv($value);
        }
    }

    public function toChannelObject()
    {
        $a = new PEAR2_Pyrus_Channel;
        $a->setName($this->getName());
        $a->setSummary($this->getSummary());
        $a->setPort($this->getPort());
        $a->setSSL($this->getSSL());
        $a->setValidationPackage();
    }

    public function __toString()
    {
        return $this->toChannelObject()->__toString();
    }

    function getMirrors()
    {
        $sql = 'SELECT server, ssl, port FROM
            channel_servers WHERE channel = \'' . $this->database->escapeString($this->channelname) .
            '\' AND server <> channel';

        $res = $this->database->query($sql);
        return $res->arrayFetch(SQLITE_ASSOC);
    }

    public function supportsREST()
    {
        $sql = '
            SELECT COUNT(*) FROM channel_server_rest WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server = \'' . $this->database->escapeString($this->mirror) . '\'';
        return (bool) $this->database->querySingle($sql);
    }

    /**
     * Get the URL to access a base resource.
     *
     * Hyperlinks in the returned xml will be used to retrieve the proper information
     * needed.  This allows extreme extensibility and flexibility in implementation
     * @param string Resource Type to retrieve
     */
    function getBaseURL($resourceType)
    {
        $sql = '
            SELECT baseurl FROM channel_server_rest WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server = \'' . $this->database->escapeString($this->mirror) . '\' AND
              type = \'' . $this->database->escapeString($resourceType) . '\'';
        return $this->database->querySingle($sql);
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        $sql = '
            DELETE FROM channel_server_rest WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server=  \'' . $this->database->escapeString($this->mirror) . '\'';
        return $this->database->exec($sql);
    }

    function setName($name)
    {
        throw new PEAR2_Pyrus_ChannelRegistry_Exception(
            'Cannot change channel name of a registered channel');
    }

    function setPort($port)
    {
        $sql = '
            UPDATE channel_servers SET port = \'' . $this->database->escapeString($port) . '\'WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server = \'' . $this->database->escapeString($this->mirror) . '\'';
        return $this->database->exec($sql);
    }

    function setSSL($ssl = true)
    {
        $ssl = $ssl ? '1' : '0';
        $sql = '
            UPDATE channel_servers SET ssl = \'' . $ssl . '\'WHERE
              channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
              server = \'' . $this->database->escapeString($this->mirror) . '\'';
        return $this->database->exec($sql);
    }

    function setBaseUrl($resourceType, $url)
    {
        $sql = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
             VALUES(\'' . $this->database->escapeString($this->channelname) . '\',
                    \'' . $this->database->escapeString($this->mirror) . '\',
                    \'' . $this->database->escapeString($resourceType) . '\',
                    \'' . $this->database->escapeString($url) . '\'';
        if (!$this->database->exec($sql)) {
            $sql = '
                UPDATE channel_server_rest
                SET baseurl = \'' . $this->database->escapeString($url) . '\' WHERE
                    channel = \'' . $this->database->escapeString($this->channelname) . '\' AND
                    type = \'' . $this->database->escapeString($resourceType) . '\' AND
                    server = \'' . $this->database->escapeString($this->mirror) . '\'';
            $this->database->exec($sql);
        }
    }

    function getAlias()
    {
        $sql = 'SELECT alias FROM channels WHERE channel = \'' . $this->database->escapeString($this->channelname) . '\'';
        return $this->database->querySingle($sql);
    }
}
