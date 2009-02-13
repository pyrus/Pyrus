--TEST--
PEAR2_Pyrus_Installer::prepare() installed package is the same or newer version
--FILE--
<?php
include dirname(__FILE__) . '/../test_framework.php.inc';
$package = new PEAR2_Pyrus_Package(__DIR__.'/../../../sandbox/SimpleChannelServer/package.xml');
@mkdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

class TestLog implements PEAR2_Pyrus_ILog
{
    protected $messages = array();
    
    function log($level, $message)
    {
        $this->messages[] = $message;
    }
    
    function getMessages()
    {
        return $this->messages;
    }
}

PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::commit();
$observer = new TestLog();
PEAR2_Pyrus_Log::attach($observer);
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::commit();
$test->assertEquals(true, in_array("Skipping installed package {$package->channel}/{$package->name}", $observer->getMessages()), 'package already installed');
$test->assertEquals(true, in_array("about to commit 0 file operations", $observer->getMessages()), 'prepare 0 file operations');
$test->assertEquals(true, in_array("successfully committed 0 file operations", $observer->getMessages()), 'commit 0 file operations');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===