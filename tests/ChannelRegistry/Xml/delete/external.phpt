--TEST--
PEAR2_Pyrus_ChannelRegistry_Xml::delete() delete external channel
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');
$chan = $creg->get('pear.unl.edu');
$creg->delete($chan);
$test->assertEquals(false, $creg->exists('pear.unl.edu'), 'successfully deleted');

?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===