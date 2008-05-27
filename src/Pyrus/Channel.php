<?php
/**
 * PEAR2_Pyrus_Channel
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
 * Base class for Pyrus.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Channel implements PEAR2_Pyrus_IChannel
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
     * Construct a PEAR2_Pyrus channel object
     *
     * @param string $data Raw channel xml
     */
    function __construct($data)
    {
        $parser = new PEAR2_Pyrus_XMLParser;
        $schema = PEAR2_Pyrus::getDataPath() . '/channel-1.0.xsd';
        // for running out of svn
        if (!file_exists($schema)) {
            $schema = dirname(__FILE__) . '/../../data/channel-1.0.xsd';
        }
        try {
            if (is_array($data)) {
                $this->channelInfo = $data;
                $this->validate();
            } else {
                $this->channelInfo = $parser->parseString($data, $schema);
                $this->channelInfo = $this->channelInfo['channel'];
            }
            // Reset root attributes.
            $this->channelInfo['attribs'] = $this->rootAttributes;
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Channel_Exception('Invalid channel.xml', $e);
        }
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
        // for running out of cvs
        if (!file_exists($schema)) {
            $schema = dirname(__FILE__) . '/../../data/channel-1.0.xsd';
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
    function getServer()
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
            return $this->channelInfo['servers']['primary']['attribs']['port'];
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

    /**
     * @param string xmlrpc or soap
     */
    function getPath($protocol)
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $protocol);
        }
        if (isset($this->channelInfo['servers']['primary'][$protocol]['attribs']['path'])) {
            return $this->channelInfo['servers']['primary'][$protocol]['attribs']['path'];
        }
        return $protocol . '.php';
    }

    /**
     * @param string protocol type (xmlrpc, soap)
     * @return array|false
     */
    function getFunctions($protocol)
    {
        if (!in_array($protocol, array('rest', 'xmlrpc', 'soap'), true)) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $protocol);
        }
        if ($this->getName() == '__uri') {
            return false;
        }
        if ($protocol == 'rest') return $this->getREST();
        if (isset($this->channelInfo['servers']['primary'][$protocol]['function'])) {
            return $this->channelInfo['servers']['primary'][$protocol]['function'];
        }

        return false;
    }

    /**
     * @param string protocol type
     * @param string protocol name
     * @param string version
     * @return boolean
     */
    function supports($type, $name = null, $version = '1.0')
    {
        $protocols = $this->getFunctions($type);
        if (!$protocols) {
            return false;
        }

        foreach ($protocols as $protocol) {
            if ($protocol['attribs']['version'] != $version) {
                continue;
            }

            if ($name === null) {
                return true;
            }

            if ($protocol['_content'] != $name) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Determines whether a channel supports Representational State Transfer (REST) protocols
     * for retrieving channel information
     * @return bool
     */
    function supportsREST()
    {
        return isset($this->channelInfo['servers']['primary']['rest']);
    }

    function getREST()
    {
        if (isset($this->channelInfo['servers']['primary']['rest'])) {
            return $this->channelInfo['servers']['primary']['rest'];
        }

        return false;
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
        $rest = $this->getREST();
        if (!isset($rest['baseurl'][0])) {
            $rest['baseurl'] = array($rest['baseurl']);
        }

        foreach ($rest['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($resourceType)) {
                return $baseurl['_content'];
            }
        }

        return false;
    }

    function __get($value)
    {
        switch ($value) {
            case 'mirrors' :
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

    /**
     * Empty all xmlrpc definitions
     */
    function resetXmlrpc()
    {
        if (isset($this->channelInfo['servers']['primary']['xmlrpc'])) {
            unset($this->channelInfo['servers']['primary']['xmlrpc']);
        }
    }

    /**
     * Empty all SOAP definitions
     */
    function resetSOAP()
    {
        if (isset($this->channelInfo['servers']['primary']['soap'])) {
            unset($this->channelInfo['servers']['primary']['soap']);
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
            return false;
        } elseif (!$this->validChannelServer($name)) {
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
     * Set the path to the entry point for a protocol
     * @param xmlrpc|soap
     * @param string
     */
    function setPath($protocol, $path)
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' . $protocol);
        }
        $this->channelInfo['servers']['primary'][$protocol]['attribs']['path'] = $path;
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
            throw new PEAR2_Pyrus_Channel_Exception('Primary server "' . $server . '" is not a valid channel server');
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
     * Add a protocol to the provides section
     * @param string protocol type
     * @param string protocol version
     * @param string protocol name
     * @return bool
     */
    function addFunction($type, $version, $name)
    {
        if (!in_array($type, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $type);
        }
        $set = array('attribs' => array('version' => $version), '_content' => $name);
        if (!isset($this->channelInfo['servers']['primary'][$type]['function'])) {
            if (!isset($this->channelInfo['servers'])) {
                $this->channelInfo['servers'] = array('primary' =>
                    array($type => array()));
            } elseif (!isset($this->channelInfo['servers']['primary'])) {
                $this->channelInfo['servers']['primary'] = array($type => array());
            }
            $this->channelInfo['servers']['primary'][$type]['function'] = $set;
        } elseif (!isset($this->channelInfo['servers']['primary'][$type]['function'][0])) {
            $this->channelInfo['servers']['primary'][$type]['function'] = array(
                $this->channelInfo['servers']['primary'][$type]['function']);
        }
        $this->channelInfo['servers']['primary'][$type]['function'][] = $set;
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
        return $this->_channelInfo;
    }

    /**
     * Retrieve the object that can be used for custom validation
     * @param string|false the name of the package to validate.  If the package is
     *                     the channel validation package, PEAR_Validate is returned
     * @return PEAR_Validate|false false is returned if the validation package
     *         cannot be located
     */
    function getValidationObject($package = false)
    {
        if (!$this->validate()) {
            return false;
        }

        if (isset($this->channelInfo['validatepackage'])) {
            if ($package == $this->channelInfo['validatepackage']) {
                // channel validation packages are always validated by PEAR_Validate
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
}
