<?php
require __DIR__ . '/../setup.php.inc';
$testpath = __DIR__ . '/testit';
mkdir($testpath);
class tc extends PEAR2_Pyrus_Config
{
    /**
     * location of PEAR2 installation
     *
     * @var string
     */
    public $pearDir;
    /**
     * location of user-specific configuration file
     *
     * @var string
     */
    public $userFile;

    public static $test;

    public static $called = 0;

    private static $paths;

    public function __construct($pearDirectory, $userfile = false)
    {
        self::$paths = array($GLOBALS['testpath'], $GLOBALS['testpath'] . PATH_SEPARATOR .
                             $GLOBALS['testpath'] . DIRECTORY_SEPARATOR . 'hi/there');
        return parent::__construct($pearDirectory, $userfile);
    }

    public function setCascadingRegistries($path)
    {
        self::$test->assertEquals(self::$paths[self::$called++], $path, "called " . (self::$called - 1));
        return parent::setCascadingRegistries($path);
    }
}
