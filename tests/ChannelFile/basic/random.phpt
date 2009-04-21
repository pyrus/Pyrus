--TEST--
ChannelFile: random channelfile tests
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$c = new PEAR2_Pyrus_ChannelFile(file_get_contents(__DIR__ . '/../../ChannelRegistry/sample_channel.xml'), true);

$test->assertEquals('pear.unl.edu', $c->name, 'verify we got the right info');

try {
    $c = new PEAR2_Pyrus_ChannelFile(false, true);
} catch (Exception $e) {
    $test->assertEquals('Unable to open channel xml file  or file was empty.', $e->getMessage(), 'error 1');
}

try {
    $c = new PEAR2_Pyrus_ChannelFile('http://greg.chiaraquartet.net/poop/channel.xml', false, true);
    $test->assertEquals(false, true, 'succeeded where it should fail');
} catch (Exception $e) {
}
?>
===DONE===
--EXPECT--
===DONE===