<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
class test_Config extends PEAR2_Pyrus_Config
{
    static function getDefaultUserConfigFile()
    {
        return MYDIR . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR . 'foo.xml';
    }
}

class test_scriptfrontend extends PEAR2_Pyrus_ScriptFrontend_Commands
{
    public static $configclass = 'test_Config';
    public static $stdin = array();
    function _readStdin()
    {
        $ret = current(self::$stdin);
        next(self::$stdin);
        return $ret;
    }
}