--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::channelDiscover() failure
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

set_include_path(TESTDIR);
$c = \PEAR2\Pyrus\Config::singleton(TESTDIR, TESTDIR . '/plugins/pearconfig.xml');
$c->bin_dir = TESTDIR . '/bin';
restore_include_path();
$c->saveConfig();

require __DIR__ . '/../../Mocks/Internet.php';

Internet::addDirectory(TESTDIR,
                       'https://pear.unl.edu/');
Internet::addDirectory(TESTDIR,
                       'http://pear.unl.edu/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
$test->assertEquals(false, isset(\PEAR2\Pyrus\Config::current()->channelregistry['pear.unl.edu']),
                    'before discover of pear.unl.edu');
ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'channel-discover', 'pear.unl.edu'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . "Discovery of channel pear.unl.edu failed: Download of http://pear.unl.edu/channel.xml failed, file does not exist\n",
                     $contents,
                    'list packages');

$test->assertEquals(false, isset(\PEAR2\Pyrus\Config::current()->channelregistry['pear.unl.edu']),
                    'after discover of pear.unl.edu');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===