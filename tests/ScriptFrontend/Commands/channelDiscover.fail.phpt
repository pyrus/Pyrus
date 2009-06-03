--TEST--
PEAR2_Pyrus_ScriptFrontend_Commands::channelDiscover() failure
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . 'testit')) {
    $dir = __DIR__ . '/testit';
    include __DIR__ . '/../../clean.php.inc';
}
mkdir(__DIR__ . DIRECTORY_SEPARATOR . 'testit');
set_include_path(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'testit');
$c = PEAR2_Pyrus_Config::singleton(__DIR__.'/testit', __DIR__ . '/testit/plugins/pearconfig.xml');
$c->bin_dir = __DIR__ . '/testit/bin';
restore_include_path();
$c->saveConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/testit',
                       'http://pear.unl.edu/');
PEAR2_Pyrus_REST::$downloadClass = 'Internet';
PEAR2_Pyrus_ScriptFrontend_Commands::$downloadClass = 'Internet';
$test->assertEquals(false, isset(PEAR2_Pyrus_Config::current()->channelregistry['pear.unl.edu']),
                    'before discover of pear.unl.edu');
ob_start();
$cli = new PEAR2_Pyrus_ScriptFrontend_Commands();
$cli->run($args = array (__DIR__ . '/testit', 'channel-discover', 'pear.unl.edu'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . __DIR__. DIRECTORY_SEPARATOR . 'testit' . "\n"
                    . "Discovery of channel pear.unl.edu failed: Download of channel.xml failed\n",
                     $contents,
                    'list packages');

$test->assertEquals(false, isset(PEAR2_Pyrus_Config::current()->channelregistry['pear.unl.edu']),
                    'after discover of pear.unl.edu');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===