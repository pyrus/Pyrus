--TEST--
\PEAR2\Pyrus\ChannelRegistry::delete() readonly test
--FILE--
<?php
mkdir(__DIR__ . '/testit');
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
// construct the registries first
$creg = new \PEAR2\Pyrus\ChannelRegistry(__DIR__ . '/testit', array('Sqlite3', 'Xml'), false);
$creg = new \PEAR2\Pyrus\ChannelRegistry(__DIR__ . '/testit', array('Sqlite3', 'Xml'), true);
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
try {
    $creg->delete($chan);
    throw new Exception('passed and shouldn\'t');
} catch (\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot delete channel, registry is read-only', $e->getMessage(), 'message');
}

try {
    unset($creg[$chan->name]);
    throw new Exception('passed and shouldn\'t');
} catch (\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot delete channel, registry is read-only', $e->getMessage(), 'message');
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