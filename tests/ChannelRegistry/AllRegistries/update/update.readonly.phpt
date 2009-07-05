--TEST--
\pear2\Pyrus\ChannelRegistry::update() readonly test
--FILE--
<?php
mkdir(__DIR__ . '/testit');
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
// construct the registries first
$creg = new \pear2\Pyrus\ChannelRegistry(__DIR__ . '/testit', array('Sqlite3', 'Xml'), false);
$creg = new \pear2\Pyrus\ChannelRegistry(__DIR__ . '/testit', array('Sqlite3', 'Xml'), true);
$chan = new \pear2\Pyrus\Channel(new \pear2\Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
try {
    $creg->update($chan);
    throw new Exception('passed and shouldn\'t');
} catch (\pear2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot update channel, registry is read-only', $e->getMessage(), 'message');
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