<?php
class PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST implements ArrayAccess
{
    protected $_info;
    
    protected $parent;
    
    protected $baseurl;
    
    function __construct($info, $parent)
    {
        $this->_info = $info;
        $this->parent = $parent;
    }
    
    function __get($var)
    {
        switch (strtolower($var)) {
            case 'baseurl':
                return $this->baseurl;
            default:
                throw new Exception($var);
        }
    }
    
    function offsetGet($protocol)
    {
        $this->baseurl = false;
        foreach ($this->_info['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                $this->baseurl = $baseurl['_content'];
                return $this;
            }
        }
        return false;
    }
    
    function offsetSet($protocol, $value)
    {
        throw new Exception('not there yet for offsetSet'.$protocol);
    }
    
    function offsetExists($protocol)
    {
        throw new Exception('not there yet for offsetExists'.$protocol);
    }
    
    function offsetUnset($protocol)
    {
        throw new Exception('not there yet for offsetunset'.$protocol);
    }
}
?>