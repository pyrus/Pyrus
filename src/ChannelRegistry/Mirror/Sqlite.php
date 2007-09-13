<?php
class PEAR2_Pyrus_ChannelRegistrymirror_Sqlite extends PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite implements PEAR2_Pyrus_Channel_IMirror
{
    private $database;
    private $_channel;
    private $_parent;
    function __construct(SQLiteDatabase $db, $mirror, PEAR2_Pyrus_IChannel $parent)
    {
        if ($parent->getName() == '__uri') {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('__uri channel cannot have mirrors');
        }
        $this->_channel = $parent->getName();
        parent::__construct($db, $this->_channel);
        $this->mirror = $mirror;
        $this->_parent = $parent;
    }

    function getChannel()
    {
        return $this->_channel;
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
        return $this->mirror;
    }
}