--TEST--
\Pyrus\ChannelRegistry\Xml::delete() readonly test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
// construct the registries first
$creg = new \Pyrus\ChannelRegistry\Xml(TESTDIR, false);
$creg = new \Pyrus\ChannelRegistry\Xml(TESTDIR, true);
$chan = new \Pyrus\Channel(new \Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
try {
    $creg->delete($chan);
    throw new Exception('passed and shouldn\'t');
} catch (\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot delete channel, registry is read-only', $e->getMessage(), 'message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===