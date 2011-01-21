--TEST--
\PEAR2\Pyrus\ChannelRegistry\Pear1::add() readonly test
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
// construct the registries first
$creg = new \PEAR2\Pyrus\ChannelRegistry\Pear1(TESTDIR, false);
$creg = new \PEAR2\Pyrus\ChannelRegistry\Pear1(TESTDIR, true);
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/../sample_channel.xml'));
try {
    $creg->add($chan);
    throw new Exception('passed and shouldn\'t');
} catch (\PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Cannot add channel, registry is read-only', $e->getMessage(), 'message');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===