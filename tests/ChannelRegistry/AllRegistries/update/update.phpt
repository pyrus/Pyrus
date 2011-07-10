--TEST--
\Pyrus\ChannelRegistry::update() basic test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

$test->assertEquals(false, $c->channelregistry->exists('pear.unl.edu'), 'channel should not exist');
$chan = new \Pyrus\Channel(new \Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel');

$test->assertEquals(80, $c->channelregistry->get('pear.unl.edu')->port, 'before');
$chan->port = 234;
$c->channelregistry->update($chan);
$test->assertEquals(234, $c->channelregistry->get('pear.unl.edu')->port, 'before');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===