<?php
require dirname(__DIR__) . '/setup.php.inc';
class r extends \Pyrus\Registry
{
    public $path;
}
class c extends \Pyrus\ChannelRegistry
{
    public $path;
}
\Pyrus\Registry::$className = 'r';
\Pyrus\ChannelRegistry::$className = 'c';
