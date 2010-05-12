<?php
include __DIR__ . '/../../test_framework.php.inc';
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
$c = \PEAR2\Pyrus\Config::singleton(__DIR__ . '/testit', __DIR__ . DIRECTORY_SEPARATOR . 'testit' .
                                   DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'foo.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

class test_Config extends \PEAR2\Pyrus\Config
{
    static function getDefaultUserConfigFile()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR .
        'plugins' . DIRECTORY_SEPARATOR . 'foo.xml';
    }
}

class test_scriptfrontend extends \PEAR2\Pyrus\ScriptFrontend\Commands
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