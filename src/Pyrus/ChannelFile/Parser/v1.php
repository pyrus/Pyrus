<?php

namespace PEAR2\Pyrus\ChannelFile\Parser;
class v1 extends \PEAR2\Pyrus\XMLParser
{

    /**
     * @param string
     * @param string file name of the channel.xml
     *
     * @return \PEAR2\Pyrus\ChannelFile\v1
     */
    function parse($data, $file = false, $class = 'PEAR2\Pyrus\ChannelFile\v1')
    {
        $ret = new $class();
        if (!$ret instanceof \PEAR2\Pyrus\ChannelFile\v1) {
            throw new \PEAR2\Pyrus\ChannelFile\Exception('Class ' . $class .
                ' passed to parse() must be a child class of \PEAR2\Pyrus\ChannelFile\v1');
        }

        $schema = \PEAR2\Pyrus\Main::getDataPath() . '/channel-1.0.xsd';
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
            throw new \PEAR2\Pyrus\ChannelFile\Exception('Invalid channel.xml', null, $e);
        }

        return $ret;
    }
}