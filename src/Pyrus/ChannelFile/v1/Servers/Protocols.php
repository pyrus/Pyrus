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