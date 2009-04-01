<?php
class PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST implements ArrayAccess, Countable
{
    protected $_info;
    protected $parent;
    protected $index;
    
    function __construct($info, $parent, $index = null)
    {
        if (isset($info['baseurl']) && !isset($info['baseurl'][0])) {
            $info['baseurl'] = array($info['baseurl']);
        }
        $this->_info = $info;
        $this->parent = $parent;
        $this->index = $index;
    }

    function count()
    {
        return count($this->_info);
    }
    
    function __get($var)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Cannot use -> to access'
                    . 'REST protocols, use []');
        }
        if ($var === 'baseurl') {
            return $this->_info['_content'];
        }
        throw new PEAR2_Pyrus_ChannelFile_Exception('Unknown variable ' . $var);
    }
    
    function offsetGet($protocol)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Cannot use [] to access'
                    . 'baseurl, use ->');
        }
        foreach ($this->_info['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                $ret = new PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST(
                    $baseurl, $this, $protocol
                );
                return $ret;
            }
        }
        return new PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST(
            array('attribs' => array('type' => $protocol), '_content' => null),
            $this, count($this->_info));
    }
    
    function offsetSet($protocol, $value)
    {
        throw new Exception('not there yet for offsetSet'.$protocol);
    }
    
    function offsetExists($protocol)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Cannot use [] to access'
                    . 'baseurl, use ->');
        }
        foreach ($this->_info['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                return true;
            }
        }
        return false;
    }
    
    function offsetUnset($protocol)
    {
        throw new Exception('not there yet for offsetunset'.$protocol);
    }
}
?>