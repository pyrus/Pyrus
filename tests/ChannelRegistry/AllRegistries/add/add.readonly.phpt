--TEST--
\PEAR2\Pyrus\ChannelRegistry::add() readonly test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
// construct the registries first
$creg = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR, array('Sqlite3', 'Xml'), false);
$creg = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR, array('Sqlite3', 'Xml'), true);
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
try {
    $creg->add($chan);
    throw new Exception('passed and shouldn\'t');
} catch (\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot add channel, registry is read-only', $e->getMessage(), 'message');
}
try {
    $creg[$chan->name] = $chan;
    throw new Exception('passed and shouldn\'t 2');
} catch (\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot add channel, registry is read-only', $e->getMessage(), 'message 2');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===