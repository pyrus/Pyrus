<?php
include __DIR__ . '/../../test_framework.php.inc';

class test_Config extends PEAR2_Pyrus_Config
{
    static function getDefaultUserConfigFile()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . 'foo.xml';
    }
}

class test_scriptfrontend extends PEAR2_Pyrus_ScriptFrontend_Commands
{
    public static $configclass = 'test_Config';
    public static $stdin = array();
    public static $index = 0;
    function _readStdin($amount = 1024)
    {
        $ret = self::$stdin[self::$index];
        echo $ret,"\n";
        if (++self::$index >= count(self::$stdin)) {
            throw new Exception('ran out of input, add more to test');
        }
        return $ret;
    }
}