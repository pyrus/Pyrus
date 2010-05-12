--TEST--
\PEAR2\Pyrus\ChannelRegistry\Pear1::update() readonly test
--FILE--
<?php
mkdir(__DIR__ . '/testit');
require dirname(dirname(__FILE__)) . '/../setup.php.inc';
// construct the registries first
$creg = new \PEAR2\Pyrus\ChannelRegistry\Pear1(__DIR__ . '/testit', false);
$creg = new \PEAR2\Pyrus\ChannelRegistry\Pear1(__DIR__ . '/testit', true);
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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===