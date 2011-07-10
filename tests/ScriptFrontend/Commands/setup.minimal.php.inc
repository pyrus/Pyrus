<?php
include __DIR__ . '/../../test_framework.php.inc';

class test_Config extends \Pyrus\Config
{
    static function getDefaultUserConfigFile()
    {
        return TESTDIR . DIRECTORY_SEPARATOR . 'foo.xml';
    }
}

class test_scriptfrontend extends \Pyrus\ScriptFrontend\Commands
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