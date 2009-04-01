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
class PEAR2_Pyrus_Channel_Mirror extends PEAR2_Pyrus_ChannelFile_v1 implements PEAR2_Pyrus_Channel_IMirror
{
    
    /**
     * Mapping of __get variables to method handlers
     * @var array
     */
    protected $getMap = array(
        'ssl' => 'getSSL',
        'port' => 'getPort',
        'server' => 'getChannel',
        'alias' => 'getAlias',
        'name' => 'getName',
        'channel' => 'getChannel',
        'mirror' => 'getServers',
        'mirrors' => 'getServers',
        'protocols' => 'getProtocols'
    );

    private $_info;
    
    /**
     * Parent channel object
     *
     * @var PEAR2_Pyrus_Channel
     */
    protected $parentChannel;
    
    function __construct($mirrorarray, PEAR2_Pyrus_IChannelFile $parent)
    {
        if ($parent->name == '__uri') {
            throw new PEAR2_Pyrus_Channel_Exception('__uri channel cannot have mirrors');
        }
        $this->_info = $mirrorarray;
        $this->parentChannel = $parent;
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
        }

        return false;
    }

    /**
     * @return int|80 port number to connect to
     */
    function getPort()
    {
        if (isset($this->_info['attribs']['port'])) {
            return (int)$this->_info['attribs']['port'];
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
        if (isset($this->_info['attribs']['ssl'])) {
            return true;
        }

        return false;
    }
    
    /**
     * Returns the protocols supported by the primary server for this channel
     * 
     * @return PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols
     */
    function getProtocols()
    {
        return new PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols($this->_info, $this->parentChannel);
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

    function setName($name)
    {
        if (empty($name)) {
            throw new PEAR2_Pyrus_Channel_Exception('Mirror server must be non-empty');
        }
        if (!$this->validChannelServer($name)) {
            throw new PEAR2_Pyrus_Channel_Exception('Mirror server "' . $name .
                '" for channel "' . $this->getChannel() . '" is not a valid channel server');
        }
        $this->_info['attribs']['host'] = $name;
        $this->save();
    }

    function setPort($port)
    {
        $this->_info['attribs']['port'] = $port;
        $this->save();
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
        $this->save();
    }

    /**
     * Empty all REST definitions
     */
    function resetREST()
    {
        if (isset($this->_info['rest'])) {
            unset($this->_info['rest']);
        }
        $this->save();
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
        $this->save();
    }

    function save()
    {
        $this->parentChannel->setMirror($this->_info);
    }
}