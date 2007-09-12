<?php
class PEAR2_Pyrus_Channel_Mirror extends PEAR2_Pyrus_Channel implements PEAR2_Pyrus_Channel_IMirror
{
    private $_info;
    private $_parent;
    function __construct(&$mirrorarray, PEAR2_Pyrus_IChannel $parent)
    {
        $this->_info = &$mirrorarray;
        $this->_parent = $parent;
        if ($parent->getName() == '__uri') {
            throw new PEAR2_Pyrus_Channel_Exception('__uri channel cannot have mirrors');
        }
    }

    function getChannel()
    {
        return $this->_parent->getName();
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
        if ($this->_parent->getName() == '__uri') {
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
}