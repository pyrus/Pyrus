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
class PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite implements PEAR2_Pyrus_IChannel, Countable
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

    function __construct(SQLiteDatabase $db, $channel)
    {
        $channel = strtolower($channel);
        $this->database = $db;
        $this->channelname = $this->mirror = $channel;
        $sql = 'SELECT channel FROM channels WHERE channel = "' . sqlite_escape_string($channel) . '"';
        if (!$this->database->singleQuery($sql)) {
            $sql = 'SELECT channel FROM channels WHERE alias = "' . sqlite_escape_string($channel) . '"';
            if (!($channel = $this->database->singleQuery($sql))) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Channel ' .
                    $this->channelname . ' does not exist');
            }
            $this->channelname = $channel;
        }
    }

    function count()
    {
        $sql = 'SELECT COUNT(*) FROM packages WHERE channel = "' . sqlite_escape_string($this->channelname) . '"';
        return $this->database->singleQuery($sql);
    }

    function getName()
    {
        return $this->channelname;
    }

    function getFunctions($protocol)
    {
        if (!in_array($protocol, array('rest'), true)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown protocol: ' .
                $protocol);
        }

        if ($this->getName() == '__uri') {
            return false;
        }

        return $this->getREST();
    }

    function setAlias($alias)
    {
        $error = '';
        $sql   = 'UPDATE channels SET alias = \'' . sqlite_escape_string($alias) . '\'';
        if (!@$this->database->queryExec($sql, $error)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot set channel ' .
                $this->channelname . ' alias to ' . $alias . ': ' . $error);
        }
    }

    function getSummary()
    {
        $sql = 'SELECT summary FROM channels WHERE channel = \'' . sqlite_escape_string($this->channelname) . '\'';
        return $this->database->singleQuery($sql);
    }

    /**
     * @return int|80 port number to connect to
     */
    function getPort()
    {
        $sql = 'SELECT port FROM channel_servers WHERE
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server = \'' . sqlite_escape_string($this->mirror) . '\'';
        return $this->database->singleQuery($sql);
    }

    function getSSL($mirror = false)
    {
        $sql = 'SELECT ssl FROM channel_servers WHERE
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server = \'' . sqlite_escape_string($this->mirror) . '\'';
        return $this->database->singleQuery($sql);
    }

    function getValidationPackage()
    {
        $sql = 'SELECT validatepackage ' .
              'FROM channels WHERE ' .
              'channel=\'' . sqlite_escape_string($this->channelname) . '\'';
        $r = $this->database->singleQuery($sql);
            if ($r == 'PEAR_Validate' || $r == 'PEAR_Validate_PECL') {
                return array('attribs' => array('version' => '1.0'), '_content' => str_replace('PEAR_', 'PEAR2_Pyrus_', $r));
            }

            $sql = 'SELECT validatepackageversion ' .
              'FROM channels WHERE ' .
              'channel=\'' . sqlite_escape_string($this->channelname) . '\'';
            $v = $this->database->singleQuery($sql);
            return array('attribs' => array('version' => $v), '_content' => $r);
    }

    public function getREST()
    {
        $sql = 'SELECT * FROM channel_server_rest WHERE
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server = \'' . sqlite_escape_string($this->mirror) . '\'';
        $urls = $this->database->arrayQuery($sql, SQLITE_ASSOC);
        $ret = array();
        foreach ($urls as $url) {
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
                      WHERE channel = \'' . sqlite_escape_string($this->channelname) . '\'
                      AND server != \'' . sqlite_escape_string($this->channelname) . '\'';
                foreach ($this->database->arrayQuery($sql) as $mirror) {
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
            channel_servers WHERE channel = \'' . sqlite_escape_string($this->channelname) .
            '\' AND server <> channel';
        return $this->database->arrayQuery($sql, SQLITE_ASSOC);
    }

    public function supportsREST()
    {
        $sql = '
            SELECT COUNT(*) FROM channel_server_rest WHERE
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server = \'' . sqlite_escape_string($this->mirror) . '\'';
        return (bool) $this->database->singleQuery($sql);
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
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server = \'' . sqlite_escape_string($this->mirror) . '\' AND
              type = \'' . sqlite_escape_string($resourceType) . '\'';
        return $this->database->singleQuery($sql);
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        $sql = '
            DELETE FROM channel_server_rest WHERE
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server=  \'' . sqlite_escape_string($this->mirror) . '\'';
        return $this->database->queryExec($sql);
    }

    function setName($name)
    {
        throw new PEAR2_Pyrus_ChannelRegistry_Exception(
            'Cannot change channel name of a registered channel');
    }

    function setPort($port)
    {
        $sql = '
            UPDATE channel_servers SET port=\'' . sqlite_escape_string($port) . '\'WHERE
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server = \'' . sqlite_escape_string($this->mirror) . '\'';
        return $this->database->queryExec($sql);
    }

    function setSSL($ssl = true)
    {
        $ssl = $ssl ? '1' : '0';
        $sql = '
            UPDATE channel_servers SET ssl=\'' . $ssl . '\'WHERE
              channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
              server = \'' . sqlite_escape_string($this->mirror) . '\'';
        return $this->database->queryExec($sql);
    }

    function setBaseUrl($resourceType, $url)
    {
        $sql = '
            INSERT INTO channel_server_rest
             (channel, server, type, baseurl)
             VALUES(\'' . sqlite_escape_string($this->channelname) . '\',
                    \'' . sqlite_escape_string($this->mirror) . '\',
                    \'' . sqlite_escape_string($resourceType) . '\',
                    \'' . sqlite_escape_string($url) . '\'';
        if (!$this->database->queryExec($sql)) {
            $sql = '
                UPDATE channel_server_rest
                SET baseurl = \'' . sqlite_escape_string($url) . '\' WHERE
                    channel = \'' . sqlite_escape_string($this->channelname) . '\' AND
                    type = \'' . sqlite_escape_string($resourceType) . '\' AND
                    server = \'' . sqlite_escape_string($this->mirror) . '\'';
            $this->database->queryExec($sql);
        }
    }

    function getAlias()
    {
        $sql = 'SELECT alias FROM channels WHERE channel = \'' . sqlite_escape_string($this->channelname) . '\'';
        return $this->database->singleQuery($sql);
    }
}
