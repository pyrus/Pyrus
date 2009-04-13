<?php

class PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols
{
    protected $_info;
    
    protected $parent;
    
    function __construct($info, $parent)
    {
        $this->_info =  $info;
        $this->parent = $parent;
    }
    
    function __get($var)
    {
        switch($var){
            case 'rest':
            case 'soap':
            case 'xmlrpc':
                $method = 'get' . $var;
                return $this->{$method}();
            default:
                throw new PEAR2_Pyrus_ChannelFile_Exception('Unknown protocol: ' . $var);
        }
    }

    function __set($var, $value)
    {
        switch($var){
            case 'rest':
            case 'soap':
            case 'xmlrpc':
                $method = 'set' . $var;
                return $this->{$method}($value);
            default:
                throw new PEAR2_Pyrus_ChannelFile_Exception('Unknown protocol: ' . $var);
        }
    }

    function setREST($value)
    {
        if ($value === null) {
            $this->parent->rest = null;
            return;
        }
        if (!($value instanceof PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST)) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Can only set REST to ' .
                        'PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST object');
        }
        $info = $value->getInfo();
        if (!count($info)) {
            $this->parent->rest = null;
        } else {
            $this->parent->rest = $info;
        }
    }

    function getREST()
    {
        if (isset($this->_info['rest'])) {
            $info = $this->_info['rest'];
        } else {
            $info = array();
        }
        return new PEAR2_Pyrus_ChannelFile_v1_Servers_Protocols_REST($info, $this->parent);
    }
}

?>