<?php
/**
 * PEAR2_Pyrus_Channel_Mirror
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
 * Class for a PEAR2 channel mirror
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Channel_Mirror extends PEAR2_Pyrus_Channel implements PEAR2_Pyrus_Channel_IMirror
{
    private $_info;
    /**
     * Parent channel object
     *
     * @var PEAR2_Pyrus_Channel
     */
    protected $parentChannel;
    function __construct(&$mirrorarray, PEAR2_Pyrus_IChannel $parent)
    {
        $this->_info = &$mirrorarray;
        $this->parentChannel = $parent;
        if ($parent->getName() == '__uri') {
            throw new PEAR2_Pyrus_Channel_Exception('__uri channel cannot have mirrors');
        }
    }

    function getChannel()
    {
        return $this->parentChannel->getName();
    }

    /**
     * @return string|false
     */
    function getName()
    {
        if (isset($this->_info['attribs']['host'])) {
            return $this->_info['attribs']['host'];
        } else {
            return false;
        }
    }

    /**
     * @return int|80 port number to connect to
     */
    function getPort()
    {
        if (isset($this->_info['attribs']['port'])) {
            return $this->_info['attribs']['port'];
        } else {
            if ($this->getSSL()) {
                return 443;
            }
            return 80;
        }
    }

    /**
     * @return bool Determines whether secure sockets layer (SSL) is used to connect to this channel
     */
    function getSSL()
    {
        if (isset($this->_info['attribs']['ssl'])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @param string xmlrpc or soap
     */
    function getPath($protocol)
    {   
        if (!in_array($protocol, array('xmlrpc', 'soap'))) {
            return false;
        }
        if (isset($this->_info[$protocol]['attribs']['path'])) {
            return $this->_info[$protocol]['attribs']['path'];
        } else {
            return $protocol . '.php';
        }
    }

    /**
     * @param string protocol type (xmlrpc, soap)
     * @return array|false
     */
    function getFunctions($protocol)
    {
        if ($this->parentChannel->getName() == '__uri') {
            return false;
        }
        if ($protocol == 'rest') {
            $function = 'baseurl';
        } else {
            $function = 'function';
        }
        if (isset($this->_info[$protocol][$function])) {
            return $this->_info[$protocol][$function];
        }
    }

    function getREST()
    {
        if (isset($this->_info['rest'])) {
            return $this->_info['rest'];
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
        return isset($this->_info['rest']);
    }

    /**
     * Empty all protocol definitions
     * @param string protocol type (xmlrpc, soap)
     */
    function resetFunctions($type)
    {
        if (isset($this->_info[$type])) {
            unset($this->_info[$type]);
        }
    }

    function setName($name)
    {
        if (empty($name)) {
            throw new PEAR2_Pyrus_Channel_Exception('Mirror server must be non-empty');
            return false;
        } elseif (!$this->validChannelServer($name)) {
            throw new PEAR2_Pyrus_Channel_Exception('Mirror server "' . $name .
                '" for channel "' . parent::getName() . '" is not a valid channel server');
        }
        $this->_info['attribs']['host'] = $name;
    }

    function setPort($port)
    {
        $this->_info['attribs']['port'] = $port;
    }

    function setSSL($ssl = true)
    {
        if (!$ssl) {
            if (isset($this->_info['attribs']['ssl'])) {
                unset($this->_info['attribs']['ssl']);
            }
        } else {
            $this->_info['attribs']['ssl'] = 'yes';
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
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $protocol);
        }
        $this->_info[$protocol]['attribs']['path'] = $path;
    }

    /**
     * Empty all xmlrpc definitions
     */
    function resetXmlrpc()
    {
        if (isset($this->_info['xmlrpc'])) {
            unset($this->_info['xmlrpc']);
        }
    }

    /**
     * Empty all SOAP definitions
     */
    function resetSOAP()
    {
        if (isset($this->_info['soap'])) {
            unset($this->_info['soap']);
        }
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        if (isset($this->_info['rest'])) {
            unset($this->_info['rest']);
        }
    }

    /**
     * Add a protocol to a mirror's provides section
     * @param string mirror name (server)
     * @param string protocol type
     * @param string protocol version
     * @param string protocol name, if any
     */
    function addFunction($type, $version, $name)
    {
        if (!in_array($type, array('xmlrpc', 'soap'))) {
            throw new PEAR2_Pyrus_Channel_Exception('Unknown protocol: ' .
                $type);
        }
        $set = array('attribs' => array('version' => $version), '_content' => $name);
        if (!isset($this->_info[$type]['function'])) {
            $this->_info[$type]['function'] = $set;
        } elseif (!isset($this->_info[$type]['function'][0])) {
            $this->_info[$type]['function'] = array($this->_info[$type]['function']);
        }
        $this->_info[$type]['function'][] = $set;
    }

    /**
     * @param string Resource Type this url links to
     * @param string URL
     */
    function setBaseURL($resourceType, $url)
    {
        $set = array('attribs' => array('type' => $resourceType), '_content' => $url);
        if (!isset($this->_info['rest'])) {
            $this->_info['rest'] = array();
        }
        if (!isset($this->_info['rest']['baseurl'])) {
            $this->_info['rest']['baseurl'] = $set;
            return;
        } elseif (!isset($this->_info['rest']['baseurl'][0])) {
            $this->_info['rest']['baseurl'] = array($this->_info['rest']['baseurl']);
        }
        foreach ($this->_info['rest']['baseurl'] as $i => $url) {
            if ($url['attribs']['type'] == $resourceType) {
                $this->_info['rest']['baseurl'][$i] = $set;
                return;
            }
        }
        $this->_info['rest']['baseurl'][] = $set;
    }
}