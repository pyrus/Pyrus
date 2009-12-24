<?php

namespace pear2\Pyrus\ChannelFile\Parser;
class v1 extends \pear2\Pyrus\XMLParser
{

    /**
     * @param string
     * @param string file name of the channel.xml
     *
     * @return \pear2\Pyrus\ChannelFile\v1
     */
    function parse($data, $file = false, $class = 'pear2\Pyrus\ChannelFile\v1')
    {
        $ret = new $class();
        if (!$ret instanceof \pear2\Pyrus\ChannelFile\v1) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Class ' . $class .
                ' passed to parse() must be a child class of \pear2\Pyrus\ChannelFile\v1');
        }
        $schema = \pear2\Pyrus\Main::getDataPath() . '/channel-1.0.xsd';
        // for running out of svn
        if (!file_exists($schema)) {
            $schema = dirname(dirname(dirname(dirname(__DIR__)))) . '/data/channel-1.0.xsd';
        }
        try {
            if ($file) {
                $ret->fromArray(parent::parse($data, $schema));
            } else {
                $ret->fromArray(parent::parseString($data, $schema));
            }
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\ChannelFile\Exception('Invalid channel.xml', null, $e);
        }
        return $ret;
    }
}