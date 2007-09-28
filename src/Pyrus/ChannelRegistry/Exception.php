<?php
class PEAR2_Pyrus_ChannelRegistry_Exception extends PEAR2_Exception
{
    public $why;
    function __construct($msg, $why)
    {
        $this->why = $why;
        parent::__construct($msg);
    }
}