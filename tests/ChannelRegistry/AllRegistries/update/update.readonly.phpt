--TEST--
\PEAR2\Pyrus\ChannelRegistry::update() readonly test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
// construct the registries first
$creg = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR, array('Sqlite3', 'Xml'), false);
$creg = new \PEAR2\Pyrus\ChannelRegistry(TESTDIR, array('Sqlite3', 'Xml'), true);
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
try {
    $creg->update($chan);
    throw new Exception('passed and shouldn\'t');
} catch (\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot update channel, registry is read-only', $e->getMessage(), 'message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===