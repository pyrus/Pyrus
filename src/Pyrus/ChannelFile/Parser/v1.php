<?php

class PEAR2_Pyrus_ChannelFile_Parser_v1 extends PEAR2_Pyrus_XMLParser
{
    /**
     * @param string
     * @param string file name of the channel.xml
     * 
     * @return PEAR2_Pyrus_ChannelFile_v1
     */
    function parse($data, $file = false)
    {
        $ret = new PEAR2_Pyrus_ChannelFile_v1();
        if (!$ret instanceof PEAR2_Pyrus_ChannelFile_v1) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Class ' . $class .
                ' passed to parse() must be a child class of PEAR2_Pyrus_ChannelFile_v1');
        }
        $schema = PEAR2_Pyrus::getDataPath() . '/channel-1.0.xsd';
        // for running out of svn
        if (!file_exists($schema)) {
            $schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/channel-1.0.xsd';
        }
        try {
            $ret->fromArray(parent::parseString($data, $schema));
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_ChannelFile_Exception('Invalid channel.xml', null, $e);
        }
        return $ret;
    }
}