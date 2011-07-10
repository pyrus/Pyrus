<?php

namespace Pyrus\ChannelFile\Parser;
class v1 extends \Pyrus\XMLParser
{

    /**
     * @param string
     * @param string file name of the channel.xml
     *
     * @return \Pyrus\ChannelFile\v1
     */
    function parse($data, $file = false, $class = 'Pyrus\ChannelFile\v1')
    {
        $ret = new $class;
        if (!$ret instanceof \Pyrus\ChannelFile\v1) {
            throw new \Pyrus\ChannelFile\Exception('Class ' . $class .
                ' passed to parse() must be a child class of \Pyrus\ChannelFile\v1');
        }

        $schema = \Pyrus\Main::getDataPath() . '/channel-1.0.xsd';
        // for running out of svn
        if (!file_exists($schema)) {
            $schema = dirname(dirname(dirname(dirname(dirname(__DIR__))))) . '/data/channel-1.0.xsd';
        }

        try {
            if ($file) {
                $ret->fromArray(parent::parse($data, $schema));
            } else {
                $ret->fromArray(parent::parseString($data, $schema));
            }
        } catch (\Exception $e) {
            throw new \Pyrus\ChannelFile\Exception('Invalid channel.xml', null, $e);
        }

        return $ret;
    }
}