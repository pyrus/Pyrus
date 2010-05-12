<?php
namespace PEAR2\Pyrus\ChannelRegistry;
class Foo extends Base
{
    static public $throw = false;
    function __construct()
    {
        if (self::$throw) {
            throw new \PEAR2\Pyrus\ChannelRegistry\Exception('foo');
        }
    }
    public function add(\PEAR2\Pyrus\ChannelInterface $channel, $update = false, $lastmodified = false){}
    public function update(\PEAR2\Pyrus\ChannelInterface $channel){}
    public function delete(\PEAR2\Pyrus\ChannelInterface $channel){}
    public function get($channel, $strict = true)
    {
        throw new Exception('buh');
    }
    public function exists($channel, $strict = true){}
    public function parseName($name){}
    public function parsedNameToString($name){}
    public function listChannels(){}
}
