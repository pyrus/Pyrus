--TEST--
\PEAR2\Pyrus\ChannelRegistry\Xml::delete() delete external channel
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');
$chan = $creg->get('pear.unl.edu');
$creg->delete($chan);
$test->assertEquals(false, $creg->exists('pear.unl.edu'), 'successfully deleted');

// for coverage
$creg = new PEAR2\Pyrus\ChannelRegistry\Xml(dirname(__DIR__) . '/testit');
$test->assertEquals(true, $creg->delete($chan), 'deleting non-existing channel');

?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===