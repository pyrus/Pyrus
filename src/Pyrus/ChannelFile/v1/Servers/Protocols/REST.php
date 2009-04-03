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

    function __set($var, $value)
    {
        if (!isset($this->index)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Cannot use -> to access'
                    . 'REST protocols, use []');
        }
        if ($var === 'baseurl') {
            $this->_info['_content'] = $value;
            return $this->save();
        }
        throw new PEAR2_Pyrus_ChannelFile_Exception('Unknown variable ' . $var);
    }
    
    function offsetGet($protocol)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Cannot use [] to access'
                    . 'baseurl, use ->');
        }
        if (!isset($this->_info['baseurl'])) {
            return new PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST(
                array('attribs' => array('type' => $protocol), '_content' => null),
                $this, 0);
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
            $this, count($this->_info['baseurl']));
    }
    
    function offsetSet($protocol, $value)
    {
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Cannot use [] to access'
                    . 'baseurl, use ->');
        }
        if (!($value instanceof self)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Can only set REST protocol ' .
                        ' to a PEAR2_Pyrus_ChannelFile_v1_Servers_Protocol_REST object');
        }
        if (!isset($this->_info['baseurl'])) {
            $this->_info['baseurl'] = $value->getInfo();
            return $this->save();
        }
        foreach ($this->_info['baseurl'] as $i => $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                $this->_info['baseurl'][$i] = $value->getInfo();
                $this->save();
            }
        }
        $this->_info['baseurl'][] = $value->getInfo();
        return $this->save();
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
        if (isset($this->index)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Cannot use [] to access'
                    . 'baseurl, use ->');
        }
        foreach ($this->_info['baseurl'] as $baseurl) {
            if (strtolower($baseurl['attribs']['type']) == strtolower($protocol)) {
                unset($this->_info['baseurl']);
                $this->_info['baseurl'] = array_values($this->_info['baseurl']);
                $this->save();
            }
        }
    }

    function getInfo()
    {
        return $this->_info;
    }

    function save()
    {
        if ($this->parent instanceof self) {
            $this->parent[$this->_info['attribs']['type']] = $this;
            return $this->parent->save();
        }
        $info = $this->_info;
        if (isset($info['baseurl']) && count($info['baseurl']) == 1) {
            $info['baseurl'] = $info['baseurl'][0];
        }
        $this->parent->rawrest = $info;
    }
}
?>