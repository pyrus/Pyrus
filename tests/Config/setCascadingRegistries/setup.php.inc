<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
class r extends \pear2\Pyrus\Registry
{
    public $parent;
    public $path;
}
class c extends \pear2\Pyrus\ChannelRegistry
{
    public $parent;
    public $path;
}
\pear2\Pyrus\Registry::$className = 'r';
\pear2\Pyrus\ChannelRegistry::$className = 'c';
