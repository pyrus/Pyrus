--TEST--
\Pyrus\ScriptFrontend\Commands::channelAdd()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$chan = \Pyrus\Config::current()->channelregistry['pecl.php.net'];
$newchan = $chan->toChannelFile();
$newchan->name = 'foobar';
$newchan->alias = 'fb';
file_put_contents(TESTDIR . '/blah.xml', $newchan);
ob_start();
$cli = new \Pyrus\ScriptFrontend\Commands(true);
$cli->run($args = array (TESTDIR, 'channel-add', TESTDIR . '/blah.xml'));

$contents = ob_get_contents();
ob_end_clean();
$test->assertEquals('Using PEAR installation found at ' . TESTDIR . "\n"
                    . "Adding channel from channel.xml:\n"
                    . "Adding channel foobar successful\n",
                    $contents,
                    'delete channel');
$chan = \Pyrus\Config::current()->channelregistry['foobar'];
$test->assertEquals('fb', $chan->alias, 'verify we got back what we added');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===