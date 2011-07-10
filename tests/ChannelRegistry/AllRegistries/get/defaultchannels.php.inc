<?php
namespace Pyrus\ChannelRegistry;
class Foo extends Base
{
    static public $throw = false;
    function __construct()
    {
        if (self::$throw) {
            throw new \Pyrus\ChannelRegistry\Exception('foo');
        }
    }
    public function add(\Pyrus\ChannelInterface $channel, $update = false, $lastmodified = false){}
    public function update(\Pyrus\ChannelInterface $channel){}
    public function delete(\Pyrus\ChannelInterface $channel){}
    public function get($channel, $strict = true)
    {
        throw new Exception('buh');
    }
    public function exists($channel, $strict = true){}
    public function parseName($name){}
    public function parsedNameToString($name){}
    public function listChannels(){}
}
