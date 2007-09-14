<?php
class PEAR2_Pyrus_ChannelRegistry_Mirror_Xml extends PEAR2_Pyrus_Channel_Mirror
{
    private $_parent;
    function __construct(&$mirrorarray, PEAR2_Pyrus_IChannel $parent,
                         PEAR2_Pyrus_ChannelRegistry $reg)
    {
        parent::__construct($mirrorarray, $parent);
        $this->_parent = $reg;
    }

    public function toChannelObject()
    {
        $chan = new PEAR2_Pyrus_Channel((string) $this->parentChannel);
        return $chan;
    }

    public function resetXmlrpc()
    {
        parent::resetXmlrpc();
        $this->_parent->update($this->parentChannel);
    }

    public function resetSOAP()
    {
        parent::resetSOAP();
        $this->_parent->update($this->parentChannel);
    }

    public function resetREST()
    {
        parent::resetREST();
        $this->_parent->update($this->parentChannel);
    }

    public function setName($name)
    {
        parent::setName($name);
        $this->_parent->update($this->parentChannel);
    }

    public function setPort($port)
    {
        parent::setPort($port);
        $this->_parent->update($this->parentChannel);
    }

    public function setSSL($ssl = true)
    {
        parent::setSSL($ssl);
        $this->_parent->update($this->parentChannel);
    }

    public function setPath($protocol, $path)
    {
        parent::setPath($protocol, $path);
        $this->_parent->update($this->parentChannel);
    }

    public function addFunction($type, $version, $name)
    {
        parent::addFunction($type, $version, $name);
        $this->_parent->update($this->parentChannel);
    }

    public function setBaseUrl($resourceType, $url)
    {
        parent::setBaseURL($resourceType, $url);
        $this->_parent->update($this->parentChannel);
    }
}