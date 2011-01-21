--TEST--
\PEAR2\Pyrus\ScriptFrontend\Commands::channelDel()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$chan = \PEAR2\Pyrus\Config::current()->channelregistry['pecl.php.net'];
$newchan = $chan->toChannelFile();
$newchan->name = 'foobar';
$newchan->alias = 'fb';
\PEAR2\Pyrus\Config::current()->channelregistry[] = $newchan;
$test->assertTrue(isset(\PEAR2\Pyrus\Config::current()->channelregistry['foobar']), 'verify we added it');

ob_start();
$cli = new \PEAR2\Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'channel-del', 'foobar'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . "Deleting channel foobar successful\n",
                    $contents,
                    'delete channel');
$test->assertFalse(isset(\PEAR2\Pyrus\Config::current()->channelregistry['foobar']), 'verify we removed it');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===