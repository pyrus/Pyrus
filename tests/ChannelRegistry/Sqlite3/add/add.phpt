--TEST--
\PEAR2\Pyrus\ChannelRegistry\Sqlite3::add() basic test
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$test->assertEquals(false, $creg->exists('pear.unl.edu'), 'channel should not exist');
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(__DIR__ . '/../../sample_channel_complex2.xml'));
$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===