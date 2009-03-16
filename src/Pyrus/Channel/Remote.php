<?php
class PEAR2_Pyrus_Channel_Remote extends PEAR2_Pyrus_Channel
{
    protected $_info;
    public $parent;
    
    /**
     * @param string $package path to package file
     */
    function __construct($channel, PEAR2_Pyrus_Channel $parent)
    {
        throw new Exception("Why in here");
        $this->_info = $channel;
        if (!is_array($channel) &&
              (preg_match('#^(http[s]?|ftp[s]?)://#', $channel))) {
            $this->internal = $this->_fromUrl($channel);
        } else {
            $this->internal = $this->_fromString($channel);
        }
        $this->from = $parent;
    }
}