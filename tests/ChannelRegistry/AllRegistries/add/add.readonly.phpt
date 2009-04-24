--TEST--
PEAR2_Pyrus_ChannelRegistry::add() readonly test
--FILE--
<?php
mkdir(__DIR__ . '/testit');
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
// construct the registries first
$creg = new PEAR2_Pyrus_ChannelRegistry(__DIR__ . '/testit', array('Sqlite3', 'Xml'), false);
$creg = new PEAR2_Pyrus_ChannelRegistry(__DIR__ . '/testit', array('Sqlite3', 'Xml'), true);
$chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
try {
    $creg->add($chan);
    throw new Exception('passed and shouldn\'t');
} catch (PEAR2_Pyrus_ChannelRegistry_Exception $e) {
    $test->assertEquals('Cannot add channel, registry is read-only', $e->getMessage(), 'message');
}
try {
    $creg[$chan->name] = $chan;
    throw new Exception('passed and shouldn\'t 2');
} catch (PEAR2_Pyrus_ChannelRegistry_Exception $e) {
    $test->assertEquals('Cannot add channel, registry is read-only', $e->getMessage(), 'message 2');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===