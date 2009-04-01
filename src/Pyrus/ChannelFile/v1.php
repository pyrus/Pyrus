<?php
/**
 * PEAR2_Pyrus_ChannelFile_v1
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
 * Base class for a PEAR channel.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelFile_v1 extends PEAR2_Pyrus_ChannelFile implements PEAR2_Pyrus_IChannelFile
{
    /**
     * Supported channel.xml versions, for parsing
     * @var array
     */
    protected $supportedVersions = array('1.0');

    /**
     * Parsed channel information
     * @var array
     */
    protected $channelInfo = array(
        'attribs' => array(
            'version' => '1.0',
            'xmlns' => 'http://pear.php.net/channel-1.0',
        ),
    );

    public $rootAttributes = array(
            'version' => '1.0',
            'xmlns' => 'http://pear.php.net/channel-1.0',
        );

    private $_xml;
    
    /**
     * Mapping of __get variables to method handlers
     * @var array
     */
    protected $getMap = array(
        'ssl'=>'getSSL',
        'port'=>'getPort',
        'server'=>'getChannel',
        'alias'=>'getAlias',
        'name'=>'getName',
        'mirror'=>'getServers',
        'mirrors'=>'getServers',
        'protocols'=>'getProtocols'
    );
    
    protected $setMap = array(
        'port'=>'setPort',
    );

    function __construct(array $data = null)
    {
        if (null !== $data) {
            $this->fromArray($data);
        }
    }
    
    
    /**
     * Directly set the channel info.
     *
     * @param array $data The xml parsed data
     */
    function fromArray($data)
    {
        if (isset($data['channel'])) {
            $this->channelInfo = $data['channel'];
        } else {
            $this->channelInfo = $data;
        }
        // Reset root attributes.
        $this->channelInfo['attribs'] = $this->rootAttributes;
    }

    /**
     * Validate the xml against the channel schema.
     *
     */
    function validate()
    {
        if (!isset($this->_xml)) {
            $this->__toString();
        }
        $a = new PEAR2_Pyrus_XMLParser;
        $schema = PEAR2_Pyrus::getDataPath() . '/channel-1.0.xsd';
        // for running out of svn
        if (!file_exists($schema)) {
            $schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/data/channel-1.0.xsd';
        }
        try {
            $a->parseString($this->_xml, $schema);
            return true;
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Channel_Exception('Invalid channel.xml', $e);
        }
    }

    /**
     * Returns the raw xml for the channel file.
     *
     * @return string
     */
    function __toString()
    {
        if (!isset($this->_xml)) {
            $this->_xml = (string) new PEAR2_Pyrus_XMLWriter(array('channel'=>$this->channelInfo));
        }
        return $this->_xml;
    }

    function toChannelObject()
    {
        return $this;
    }

    /**
     * @return string|false
     */
    function getName()
    {
        if (isset($this->channelInfo['name'])) {
            return $this->channelInfo['name'];
        }

        return false;
    }

    /**
     * @return string|false
     */
    function getSummary()
    {
        if (isset($this->channelInfo['summary'])) {
            return $this->channelInfo['summary'];
        }

        return false;
    }

    /**
     * @return int|80 port number to connect to
     */
    function getPort()
    {
        if (isset($this->channelInfo['servers']['primary']['attribs']['port'])) {
            return (int)$this->channelInfo['servers']['primary']['attribs']['port'];
        }

        if ($this->getSSL()) {
            return 443;
        }

        return 80;
    }

    /**
     * @return bool Determines whether secure sockets layer (SSL) is used to connect to this channel
     */
    function getSSL()
    {
        if (isset($this->channelInfo['servers']['primary']['attribs']['ssl'])) {
            return true;
        }

        return false;
    }

    function __get($var)
    {
        if (isset($this->getMap[$var])) {
            return $this->{$this->getMap[$var]}($var);
        }
        return $this->channelInfo[$var];
    }
    
    /**
     * Returns the protocols supported by the primary server for this channel
     *
     * @return PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols
     */
    function getProtocols()
    {
        if ($this->channelInfo['name'] == '__uri') {
            throw new PEAR2_Pyrus_Channel_Exception('__uri pseudo-channel has no protocols');
        }
        return new PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols($this->channelInfo['servers']['primary'], $this);
    }
    
    function getServers()
    {
        if ($this->channelInfo['name'] == '__uri') {
            throw new PEAR2_Pyrus_Channel_Exception('__uri pseudo-channel has no mirrors');
        }
        if (isset($this->channelInfo['servers'])) {
            $servers = $this->channelInfo['servers'];
        } else {
            $servers = array();
        }
        return new PEAR2_Pyrus_ChannelFile_v1_Servers($this->channelInfo['servers'], $this);
    }
    
    /**
     * Determines whether a channel supports Representational State Transfer (REST) protocols
     * for retrieving channel information
     *
     * @return bool
     */
    function supportsREST()
    {
        return isset($this->channelInfo['servers']['primary']['rest']);
    }
    
    /**
     * Returns a mirror, if any
     *
     * @param string $name Name of the mirror
     * @return unknown_type
     */
    function getMirror($name = null)
    {
        if (!isset($this->channelInfo['servers']['mirror'])) {
            return array();
        }
        if (!isset($this->channelInfo['servers']['mirror'][0])) {
            return array(
              $this->channelInfo['servers']['mirror']['attribs']['host'] =>
              new PEAR2_Pyrus_Channel_Mirror(
                  $this->channelInfo['servers']['mirror'], $this));
        }
        $ret = array();

        foreach ($this->channelInfo['servers']['mirror'] as $i => $mir) {
            $ret[$mir['attribs']['host']] = new PEAR2_Pyrus_Channel_Mirror(
                  $this->channelInfo['servers']['mirror'][$i], $this);
        }
        return $ret;
    }

    function __set($var, $value)
    {
        if (isset($this->setMap[$var])) {
            return $this->{$this->setMap[$var]}($value);
        }
        if (method_exists($this, "set$var")) {
            $sv = "set$var";
            $this->$sv($value);
        }
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        if (isset($this->channelInfo['servers']['primary']['rest'])) {
            unset($this->channelInfo['servers']['primary']['rest']);
        }
    }

    /**
     * @param string
     * @return string|false
     * @error PEAR_CHANNELFILE_ERROR_NO_NAME
     * @error PEAR_CHANNELFILE_ERROR_INVALID_NAME
     */
    function setName($name)
    {
        if (empty($name)) {
            throw new PEAR2_Pyrus_Channel_Exception('Primary server must be non-empty');
        }
        if (!$this->validChannelServer($name)) {
            throw new PEAR2_Pyrus_Channel_Exception('Primary server "' . $name .
                '" is not a valid channel server');
        }
        $this->channelInfo['name'] = $name;
    }

    /**
     * Test whether a string contains a valid channel server.
     * @param string $ver the package version to test
     * @return bool
     */
    static function validChannelServer($server)
    {
        if ($server == '__uri') {
            return true;
        }

        $regex = '/^[a-z0-9\-]+(?:\.[a-z0-9\-]+)*(\/[a-z0-9\-]+)*\\z/i';
        return (bool) preg_match($regex, $server);
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param integer
     */
    function setPort($port)
    {
        $this->channelInfo['servers']['primary']['attribs']['port'] = $port;
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param bool Determines whether to turn on SSL support or turn it off
     */
    function setSSL($ssl = true)
    {
        if ($ssl) {
            $this->channelInfo['servers']['primary']['attribs']['ssl'] = 'yes';
        } elseif (isset($this->channelInfo['servers']['primary']['attribs']['ssl'])) {
            unset($this->channelInfo['servers']['primary']['attribs']['ssl']);
        }
    }

    /**
     * @param string
     * @return boolean success
     * @error PEAR_CHANNELFILE_ERROR_NO_SUMMARY
     * @warning PEAR_CHANNELFILE_ERROR_MULTILINE_SUMMARY
     */
    function setSummary($summary)
    {
        if (empty($summary)) {
            throw new PEAR2_Pyrus_Channel_Exception('Channel summary cannot be empty');
        } elseif (strpos(trim($summary), "\n") !== false) {
            // not sure what to do about this yet
            $this->_validateWarning(PEAR_CHANNELFILE_ERROR_MULTILINE_SUMMARY,
                array('summary' => $summary));
        }
        $this->channelInfo['summary'] = $summary;
        return true;
    }

    /**
     * @param string
     * @param boolean determines whether the alias is in channel.xml or local
     * @return boolean success
     */
    function setAlias($alias, $local = false)
    {
        if (!$this->validChannelServer($alias)) {
            throw new PEAR2_Pyrus_Channel_Exception('Alias "' . $alias . '" is not a valid channel alias');
        }

        $a = $local ? 'localalias' : 'suggestedalias';
        $this->channelInfo[$a] = $alias;
        return true;
    }

    /**
     * @return string
     */
    function getAlias()
    {
        if (isset($this->channelInfo['localalias'])) {
            return $this->channelInfo['localalias'];
        }

        if (isset($this->channelInfo['suggestedalias'])) {
            return $this->channelInfo['suggestedalias'];
        }

        if (isset($this->channelInfo['name'])) {
            return $this->channelInfo['name'];
        }

        return '';
    }

    /**
     * Set the package validation object if it differs from PEAR's default
     * The class must be includeable via changing _ in the classname to path separator,
     * but no checking of this is made.
     * @param string|false pass in false to reset to the default packagename regex
     * @return boolean success
     */
    function setValidationPackage($validateclass, $version)
    {
        if (empty($validateclass)) {
            unset($this->channelInfo['validatepackage']);
        }
        $this->channelInfo['validatepackage'] = array('_content' => $validateclass);
        $this->channelInfo['validatepackage']['attribs'] = array('version' => $version);
    }

    /**
     * @param string Resource Type this url links to
     * @param string URL
     */
    function setBaseURL($resourceType, $url)
    {
        $set = array('attribs' => array('type' => $resourceType), '_content' => $url);
        if (!isset($this->channelInfo['servers']['primary']['rest'])) {
            $this->channelInfo['servers']['primary']['rest'] = array();
        }
        if (!isset($this->channelInfo['servers']['primary']['rest']['baseurl'])) {
            $this->channelInfo['servers']['primary']['rest']['baseurl'] = $set;
            return;
        } elseif (!isset($this->channelInfo['servers']['primary']['rest']['baseurl'][0])) {
            $this->channelInfo['servers']['primary']['rest']['baseurl'] = array($this->channelInfo['servers']['primary']['rest']['baseurl']);
        }
        foreach ($this->channelInfo['servers']['primary']['rest']['baseurl'] as $i => $url) {
            if ($url['attribs']['type'] == $resourceType) {
                $this->channelInfo['servers']['primary']['rest']['baseurl'][$i] = $set;
                return;
            }
        }
        $this->channelInfo['servers']['primary']['rest']['baseurl'][] = $set;
    }

    /**
     * @param string mirror server
     * @param int mirror http port
     * @return boolean
     */
    function addMirror($server, $port = null)
    {
        if ($this->channelInfo['name'] == '__uri') {
            return false; // the __uri channel cannot have mirrors by definition
        }

        $set = array('attribs' => array('host' => $server));
        if (is_numeric($port)) {
            $set['attribs']['port'] = $port;
        }

        if (!isset($this->channelInfo['servers']['mirror'])) {
            $this->channelInfo['servers']['mirror'] = $set;
            return true;
        }

        if (!isset($this->channelInfo['servers']['mirror'][0])) {
            $this->channelInfo['servers']['mirror'] =
                array($this->channelInfo['servers']['mirror']);
        }

        $this->channelInfo['servers']['mirror'][] = $set;
        return true;
    }

    /**
     * Retrieve the name of the validation package for this channel
     * @return string|false
     */
    function getValidationPackage()
    {
        if (!$this->validate()) {
            return false;
        }

        if (!isset($this->channelInfo['validatepackage'])) {
            return array('attribs' => array('version' => 'default'),
                '_content' => 'PEAR2_Pyrus_Validate');
        }

        return $this->channelInfo['validatepackage'];
    }

    function getArray()
    {
        return $this->channelInfo;
    }

    /**
     * Retrieve the object that can be used for custom validation
     * @param string|false the name of the package to validate.  If the package is
     *                     the channel validation package, PEAR_Validate is returned
     * @return PEAR2_Pyrus_Validate|false false is returned if the validation package
     *         cannot be located
     */
    function getValidationObject($package = false)
    {
        if (!$this->validate()) {
            return false;
        }

        if (isset($this->channelInfo['validatepackage'])) {
            if ($package == $this->channelInfo['validatepackage']['_content']) {
                // channel validation packages are always validated by PEAR2_Pyrus_Validate
                $val = new PEAR2_Pyrus_Validate;
                return $val;
            }

            if (!class_exists(str_replace('.', '_',
                  $this->channelInfo['validatepackage']['_content']), true)) {
                return false;
            }

            $vclass = str_replace('.', '_',
                $this->channelInfo['validatepackage']['_content']);
            $val = new $vclass;
        } else {
            $val = new PEAR2_Pyrus_Validate;
        }
        return $val;
    }

    /**
     * This function is used by the channel updater and retrieves a value set by
     * the registry, or the current time if it has not been set
     * @return string
     */
    function lastModified()
    {
        if (isset($this->channelInfo['_lastmodified'])) {
            return $this->channelInfo['_lastmodified'];
        }

        return time();
    }

    function setMirror($info)
    {
        if (!isset($this->channelInfo['servers'])) {
            $this->channelInfo['servers'] = array('mirror' => $info);
            return;
        }
        if (!isset($this->channelInfo['servers']['mirror'])) {
            $this->channelInfo['servers']['mirror'] = $info;
        }
        if (!isset($this->channelInfo['servers']['mirror'][0])) {
            if ($this->channelInfo['servers']['mirror']['attribs']['host'] != $info['attribs']['host']) {
                $this->channelInfo['servers']['mirror'] = array($this->channelInfo['servers']['mirror']);
                $this->channelInfo['servers']['mirror'][] = $info;
                return;
            }
            $this->channelInfo['servers']['mirror'] = $info;
            return;
        }
        foreach ($this->channelInfo['servers']['mirror'] as $i => $mirror) {
            if ($mirror['attribs']['host'] == $info['attribs']['host']) {
                $this->channelInfo['servers']['mirror'][$i] = $info;
                return;
            }
        }
        $this->channelInfo['servers']['mirror'][] = $info;
    }
}
