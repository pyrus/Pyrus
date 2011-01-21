--TEST--
\PEAR2\Pyrus\ChannelRegistry::delete() delete external channel
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel');
$chan = $c->channelregistry->get('pear.unl.edu');
$c->channelregistry->delete($chan);
$test->assertEquals(false, $c->channelregistry->exists('pear.unl.edu'), 'successfully deleted');

$c->channelregistry->add($chan);
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel 2');
unset($c->channelregistry[$chan->name]);
$test->assertEquals(false, $c->channelregistry->exists('pear.unl.edu'), 'successfully deleted 2');

$c->channelregistry[$chan->name] = $chan;
$test->assertEquals(true, $c->channelregistry->exists('pear.unl.edu'), 'successfully added the channel 3');
unset($c->channelregistry[$chan->alias]);
$test->assertEquals(false, $c->channelregistry->exists('pear.unl.edu'), 'successfully deleted 3');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===