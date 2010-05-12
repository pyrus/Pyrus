--TEST--
\PEAR2\Pyrus\ChannelRegistry\Xml::update() basic test
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$test->assertEquals(false, $creg->exists('pear.unl.edu'), 'channel should not exist');
$chan = new \PEAR2\Pyrus\Channel(new \PEAR2\Pyrus\ChannelFile(__DIR__ . '/../../sample_channel_complex2.xml'));

try {
    $creg->update($chan);
    throw new Exception('worked and should fail');
} catch (PEAR2\Pyrus\ChannelRegistry\Exception $e) {
    $test->assertEquals('Error: channel pear.unl.edu is unknown', $e->getMessage(),
                        'exception');
}

$creg->add($chan);
$test->assertEquals(true, $creg->exists('pear.unl.edu'), 'successfully added the channel');

$test->assertEquals(80, $creg->get('pear.unl.edu')->port, 'before');
$chan->port = 234;
$creg->update($chan);
$test->assertEquals(234, $creg->get('pear.unl.edu')->port, 'before');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===