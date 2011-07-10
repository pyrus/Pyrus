--TEST--
\Pyrus\ChannelRegistry::exists() basic test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

$test->assertEquals(false, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel');
$chan = new \Pyrus\Channel(new \Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu', false), 'successfully added the channel');
$test->assertEquals(true, $c->channelregistry->exists('unl', false), 'successfully added the channel');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===