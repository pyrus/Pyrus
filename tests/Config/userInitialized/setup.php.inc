<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
class r extends \pear2\Pyrus\Config
{
    static $foo = 1;
    static function getDefaultUserConfigFile()
    {
        if (self::$foo == 1) return __DIR__ . '/testing/oops';
        return __DIR__ . '/testing/myfile.xml';
    }
}
