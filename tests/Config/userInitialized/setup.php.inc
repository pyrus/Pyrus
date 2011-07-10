<?php
require dirname(__DIR__) . '/setup.php.inc';
class r extends \Pyrus\Config
{
    static $foo = 1;
    static function getDefaultUserConfigFile()
    {
        if (self::$foo == 1) return TESTDIR . '/testing/oops';
        return TESTDIR . '/testing/myfile.xml';
    }
}
