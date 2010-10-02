<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
class r extends \PEAR2\Pyrus\Registry
{
    public $path;
}
class c extends \PEAR2\Pyrus\ChannelRegistry
{
    public $path;
}
\PEAR2\Pyrus\Registry::$className = 'r';
\PEAR2\Pyrus\ChannelRegistry::$className = 'c';
