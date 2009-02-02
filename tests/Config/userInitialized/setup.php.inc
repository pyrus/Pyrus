<?php
require dirname(dirname(__FILE__)) . '/setup.php.inc';
class r extends PEAR2_Pyrus_Config
{
    static $foo = 1;
    static function getDefaultUserConfigFile()
    {
        if (self::$foo == 1) return __DIR__ . '/oops';
        return __DIR__ . '/myfile.xml';
    }
}
