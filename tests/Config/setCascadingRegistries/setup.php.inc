<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
class r extends PEAR2_Pyrus_Registry
{
    public $parent;
    public $path;
}
class c extends PEAR2_Pyrus_ChannelRegistry
{
    public $parent;
    public $path;
}
PEAR2_Pyrus_Registry::$className = 'r';
PEAR2_Pyrus_ChannelRegistry::$className = 'c';
