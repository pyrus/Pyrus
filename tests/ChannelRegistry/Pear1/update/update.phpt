--TEST--
\PEAR2\Pyrus\ChannelRegistry\Pear1::update() basic test
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$test->assertEquals(false, $creg->exists('pear.unl.edu'), 'channel should not exist');
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(__DIR__ . '/../../sample_channel_complex2.xml'));
$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');

$test->assertEquals(80, $creg->get('pear.unl.edu')->port, 'before');
$chan->port = 234;
$chan->alias = 'burp';
$test->assertEquals('pear.unl.edu', $creg->channelFromAlias('unl'), 'before');
$test->assertEquals('burp', $creg->channelFromAlias('burp'), 'before');
$creg->update($chan);
$test->assertEquals('pear.unl.edu', $creg->channelFromAlias('burp'), 'after');
$test->assertEquals('unl', $creg->channelFromAlias('unl'), 'after');
$test->assertEquals(234, $creg->get('pear.unl.edu')->port, 'after');

$chan->alias = 'pear';
$test->assertEquals('pear.php.net', $creg->channelFromAlias('pear'), 'before 2');
$test->assertEquals('pear.unl.edu', $creg->channelFromAlias('burp'), 'before 2');
$creg->add($chan, true, 'hi');
$test->assertEquals('burp', $creg->channelFromAlias('burp'), 'after 2');
$test->assertEquals('pear.php.net', $creg->channelFromAlias('pear'), 'after 2');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===