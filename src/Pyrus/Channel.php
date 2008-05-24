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
     * @var DOMDocument
     */
    protected $channelInfo = '<?xml version="1.0" encoding="UTF-8" ?>
        <channel version="1.0" xmlns="http://pear.php.net/channel-1.0">
         <name/>
         <summary/>
         <servers>
          <primary/>
         </servers>
        </channel>';

    public $rootAttributes = array(
            'version' => '1.0',
            'xmlns' => 'http://pear.php.net/channel-1.0',
        );

    private $_xml;
    protected $xpath;

    /**
     * Construct a PEAR2_Pyrus channel object
     *
     * @param string $data Raw channel xml
     */
    function __construct($data = null)
    {
        try {
            if (null === $data) {
                $a = new DOMDocument;
                $a->loadXML($this->channelInfo);
                $this->channelInfo = $a;
                return;
            } elseif ($data instanceof DOMDocument) {
                $this->channelInfo = $data;
            } else {
                $this->channelInfo = new DOMDocument;
                libxml_use_internal_errors(true);
                libxml_clear_errors();
                $this->channelInfo->loadXML($data, LIBXML_NOCDATA|LIBXML_NOBLANKS);
                $causes = array();
                foreach (libxml_get_errors() as $error) {
                    $causes[] = new PEAR2_Pyrus_Channel_Exception("Line " .
                         $error->line . ': ' . $error->message);
                }
                libxml_clear_errors();
                if (count($causes)) {
                    throw new PEAR2_Pyrus_Channel_Exception('Invalid XML document', $causes);
                }
            }
            $this->validate();
            $this->xpath = new DOMXPath($this->channelInfo);
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
        $schema = PEAR2_Pyrus::getDataPath() . '/channel-1.0.xsd';
        // for running out of svn
        if (!file_exists($schema)) {
            $schema = dirname(dirname(dirname(__FILE__))) . '/data/channel-1.0.xsd';
        }
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $this->channelInfo->schemaValidate($schema);
        $causes = array();
        foreach (libxml_get_errors() as $error) {
            $causes[] = new PEAR2_Pyrus_Channel_Exception("Line " .
                 $error->line . ': ' . $error->message);
        }
        libxml_clear_errors();
        if (count($causes)) {
            throw new PEAR2_Pyrus_Channel_Exception('Invalid XML document', $causes);
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
        $name = $this->xpath->query('//channel/name/text()');
        if ($name->item(0)) {
            return trim($name->item(0)->nodeValue);
        }
        return false;
    }

    /**
     * @return string|false
     */
    function getServer()
    {
        return $this->getName();
    }

    /**
     * @return string|false
     */
    function getSummary()
    {
        $name = $this->xpath->query('//channel/summary/text()');
        if (!$name->length) return false;
        return trim($name->item(0)->nodeValue);
    }

    /**
     * @return int|80 port number to connect to
     */
    function getPort()
    {
        $name = $this->xpath->query('//channel/servers/primary/@port');
        if ($name->length) {
            return trim($name->item(0)->nodeValue);
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
        $name = $this->xpath->query('//channel/servers/primary[@ssl="yes"]');
        if ($name->length) {
            return true;
        }

        return false;
    }

    /**
     * @param string xmlrpc or soap
     */
    function getPath($protocol)
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'), true)) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $protocol);
        }
        $name = $this->xpath->query('//channel/servers/primary/' . $protocol . '/@path');
        if ($name->length) {
            return trim($name->item(0)->nodeValue);
        }
        return $protocol . '.php';
    }

    /**
     * @param string protocol type (xmlrpc, soap)
     * @return DOMNodeList|false
     */
    function getFunctions($protocol)
    {
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $protocol);
        }
        if ($this->getName() == '__uri') {
            return false;
        }
        if (!in_array($protocol, array('xmlrpc', 'soap'), true)) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $protocol);
        }
        $name = $this->xpath->query('//channel/servers/primary/' . $protocol . '/@path');
        if ($name->length) {
            return trim($name->item(0)->nodeValue);
        }
        return $protocol . '.php';
        if (!in_array($protocol, array('xmlrpc', 'soap'), true)) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $protocol);
        }
        $name = $this->xpath->query('//channel/servers/primary/' . $protocol . '/function');
        if ($name->length) {
            return $name;
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

        $node = $this->xpath->query('//function[@version="' . $version . '" and text()="' .
                             $name . '"]', $protocols);
        if ($node->length) {
            return false;
        }
        return true;
    }

    /**
     * Determines whether a channel supports Representational State Transfer (REST) protocols
     * for retrieving channel information
     * @return bool
     */
    function supportsREST()
    {
        return (bool) $this->xpath->query('//channel/servers/primary/rest')->length;
    }

    function getREST()
    {
        $ret = $this->xpath->query('//channel/servers/primary/rest');
        if (!$ret->length) {
            return false;
        }
        return $ret;
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

        if (!$rest) return false;

        $ret = $this->xpath->query('//baseurl[@type="' . $resourceType . '"]');
        if ($ret->length) {
            return trim($ret->item(0)->nodeValue);
        }

        return false;
    }

    function __get($value)
    {
        switch ($value) {
            case 'mirrors' :
                if ($mirrors = $this->xpath->query('//channel/servers/mirror')) {
                    $ret = array();
                    foreach($mirrors as $node) {
                        $ret[$node->getAttribute('host')] = new PEAR2_Pyrus_Channel_Mirror(
                            $node, $this);
                    }
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
        $ret = $this->xpath->query('//channel/servers/primary/xmlrpc');
        if ($ret) {
            foreach ($ret as $node) {
                $this->channelInfo->removeChild($node);
            }
        }
    }

    /**
     * Empty all SOAP definitions
     */
    function resetSOAP()
    {
        $ret = $this->xpath->query('//channel/servers/primary/soap');
        if ($ret) {
            foreach ($ret as $node) {
                $this->channelInfo->removeChild($node);
            }
        }
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        $ret = $this->xpath->query('//channel/servers/primary/rest');
        if ($ret) {
            foreach ($ret as $node) {
                $this->channelInfo->removeChild($node);
            }
        }
    }

    private function _setNode($tag, $value, $after = null)
    {
        $node = $this->channelInfo->createElement($tag, $value);

        if ($list = $this->xpath->query('//channel/' . $tag)) {
            $list->item(0)->nodeValue = $value;
            return;
        }

        $node = $this->channelInfo->createElement($tag, $value);
        if ($after && $next = $this->xpath->query('//channel/' . $after)) {
            $this->channelInfo->insertBefore($node, $next);
            return;
        }

        // no tags exist
        if ($this->channelInfo->firstChild) {
            $this->channelInfo->insertBefore($node, $this->channelInfo->firstChild);
        } else {
            $this->channelInfo->appendChild($node);
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
        $this->_setNode('name', $name);
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
        $sxml = simplexml_import_dom($this->channelInfo);
        $sxml->channel->servers->primary->addAttribute('port', $port);
    }

    /**
     * Set the socket number (port) that is used to connect to this channel
     * @param bool Determines whether to turn on SSL support or turn it off
     */
    function setSSL($ssl = true)
    {
        $sxml = simplexml_import_dom($this->channelInfo);
        if ($ssl) {
            $sxml->channel->servers->primary->addAttribute('ssl', 'yes');
        } else {
            if (isset($sxml->channel->servers->primary['ssl'])) {
                unset($sxml->channel->servers->primary['ssl']);
            }
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
        $sxml = simplexml_import_dom($this->channelInfo);
        $sxml->channel->servers->primary->$protocol->addAttribute('path', $path);
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
        $this->_setNode('summary', $summary, 'suggestedalias');
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
        $sxml = simplexml_import_dom($this->channelInfo);
        $sxml->channel->servers->primary->addAttribute('port', $port);
        return true;
    }

    /**
     * @return string
     */
    function getAlias()
    {
        $sxml = simplexml_import_dom($this->channelInfo);
        if (isset($sxml->channel->localalias)) {
            return (string) $sxml->channel->localalias;
        }
        if (isset($sxml->channel->suggestedalias)) {
            return (string) $sxml->channel->suggestedalias;
        }
        if (isset($sxml->channel->name)) {
            return (string) $sxml->channel->name;
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
        $vc = $this->xpath($this->channelInfo, '//channel/validatepackage');

        if (empty($validateclass)) {
            if (!$vc) return;
            $this->channelInfo->removeChild($vc);
            return;
        }
        $this->channelInfo->removeChild($vc);
        $vc = $this->channelInfo->createElement('validatepackage', $validateclass);
        $ver = $this->channelInfo->createAttribute('version');
        $ver->nodeValue = $version;
        $vc->appendChild($ver);
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
        $func = $this->xpath->query('//channel/servers/primary/' . $type .
                                    '/function[@version="' . $version .
                                    '" and text()="' . $name . '"]');
        if ($func->length) {
            return;
        }
        $thing = $this->xpath->query('//channel/servers/primary/' . $type);
        if (!$thing->length) {
            $thing = $this->xpath->query('//channel/servers/primary');
            $proto = $this->channelInfo->createElement($type, '');
            $thing->item(0)->appendChild($proto);
            $thing = $proto;
        } else {
            $thing = $thing->item(0);
        }

        $set = $this->channelInfo->createElement('function', $name);
        $set->appendChild($this->channelInfo->createAttribute('version', $version));
        $thing->appendChild($set);
    }

    /**
     * @param string Resource Type this url links to
     * @param string URL
     */
    function setBaseURL($resourceType, $url)
    {
        $func = $this->xpath->query('//channel/servers/primary/rest' .
                                    '/baseurl[@type="' . $resourceType .
                                    '" and text()="' . $url . '"]');
        if ($func->length) {
            return;
        }
        $thing = $this->xpath->query('//channel/servers/primary/rest');
        if (!$thing->length) {
            $thing = $this->xpath->query('//channel/servers/primary');
            $proto = $this->channelInfo->createElement('rest', '');
            $thing->item(0)->appendChild($proto);
            $thing = $proto;
        } else {
            $thing = $thing->item(0);
        }

        $set = $this->channelInfo->createElement('baseurl', $name);
        $set->appendChild($this->channelInfo->createAttribute('type', $version));
        $thing->appendChild($set);
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
        if (!$this->_isValid && !$this->validate()) {
            return false;
        }

        $name = $this->xpath->query('//channel/validatepackage');
        if (!$name->length) {
            return array('attribs' => array('version' => 'default'),
                '_content' => 'PEAR2_Pyrus_Validate');
        }

        $ret = array('attribs' =>
                     array('version' => $name->item(0)->getAttribute('version')),
                     '_content' => $this->xpath->query('//channel/validatepackage/text()')
                     ->item(0)->nodeValue);

        return $ret;
    }

    function getArray()
    {
        // TODO: make this return XML_Unserializer format
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
        if (!$this->_isValid && !$this->validate()) {
            return false;
        }

        $vp = $this->getValidationPackage();
        if ($package == $this->channelInfo['validatepackage']) {
            // channel validation packages are always validated by PEAR_Validate
            $val = new PEAR2_Pyrus_Validate;
            return $val;
        }

        if (!class_exists(str_replace('.', '_',
              $vp['_content']), true)) {
            return false;
        }

        $vclass = str_replace('.', '_', $vp['_content']);
        if (!class_exists($vclass, true)) {
            $vclass = str_replace('.', '::', $vp['_content']);
        }
        $val = new $vclass;

        return $val;
    }

    /**
     * This function is used by the channel updater and retrieves a value set by
     * the registry, or the current time if it has not been set
     * @return string
     */
    function lastModified()
    {
        if ($x = $this->xpath->query('//channel/_lastmodified')) {
            return $x->item(0)->nodeValue;
        }

        return time();
    }
}
