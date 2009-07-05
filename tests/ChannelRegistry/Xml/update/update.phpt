--TEST--
\pear2\Pyrus\ChannelRegistry\Xml::update() basic test
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$test->assertEquals(false, $creg->exists('pear.unl.edu'), 'channel should not exist');
$chan = new \pear2\Pyrus\Channel(new \pear2\Pyrus\ChannelFile(__DIR__ . '/../../sample_channel_complex2.xml'));
$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');

$test->assertEquals(80, $creg->get('pear.unl.edu')->port, 'before');
$chan->port = 234;
$creg->update($chan);
$test->assertEquals(234, $creg->get('pear.unl.edu')->port, 'before');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===