<?php
class PEAR2_Pyrus_Channel_Mirror extends PEAR2_Pyrus_Channel_Base
{
    private $_info;
    private $_parent;
    function __construct($mirrorarray, PEAR2_Pyrus_IChannel $parent)
    {
        $this->_info = $mirrorarray;
        $this->_parent = $parent;
    }

    function __toString()
    {
        if (!isset($this->_xml)) {
            $this->_xml = (string) new PEAR2_Pyrus_XMLWriter($this->_info);
        }
        return $this->_xml;
    }

    function toChannelObject()
    {
        return $parent;
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
     * @return string|false
     */
    function getSummary()
    {
        return $this->_parent->getSummary();
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
}