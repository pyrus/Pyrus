<?php
class PEAR2_Pyrus_ChannelRegistry_Channel_Xml extends PEAR2_Pyrus_Channel
{
    private $_parent;
    function __construct(PEAR2_Pyrus_ChannelRegistry_Xml $parent, $data)
    {
        $this->_parent = $parent;
        parent::__construct($data);
    }

    public function toChannelObject()
    {
        $chan = new PEAR2_Pyrus_Channel((string) $this);
        return $chan;
    }

    public function resetXmlrpc()
    {
        parent::resetXmlrpc();
        $this->_parent->update($this);
    }

    public function resetSOAP()
    {
        parent::resetSOAP();
        $this->_parent->update($this);
    }

    public function resetREST()
    {
        parent::resetREST();
        $this->_parent->update($this);
    }

    public function setName($name)
    {
        parent::setName($name);
        $this->_parent->update($this);
    }

    public function setPort($port)
    {
        parent::setPort($port);
        $this->_parent->update($this);
    }

    public function setSSL($ssl = true)
    {
        parent::setSSL($ssl);
        $this->_parent->update($this);
    }

    public function setPath($protocol, $path)
    {
        parent::setPath($protocol, $path);
        $this->_parent->update($this);
    }

    public function addFunction($type, $version, $name)
    {
        parent::addFunction($type, $version, $name);
        $this->_parent->update($this);
    }

    public function setBaseUrl($resourceType, $url)
    {
        parent::setBaseURL($resourceType, $url);
        $this->_parent->update($this);
    }

    public function setAlias($alias)
    {
        @unlink($this->_parent->getAliasFile($this->getAlias()));
        parent::setAlias($alias);
        file_put_contents($this->getAliasFile($alias), $this->getName());
    }
}