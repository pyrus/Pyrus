--TEST--
\Pyrus\ChannelRegistry\Xml::listChannels() installed channels
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$test->assertEquals(false, $creg->exists('pear.unl.edu'), 'channel should not exist');
$chan = new \Pyrus\Channel(new \Pyrus\ChannelFile(__DIR__ . '/../../sample_channel_complex2.xml'));
$creg->add($chan);

$chans = $creg->listChannels();
sort($chans);

$test->assertEquals(array(
    '__uri',
    'doc.php.net',
    'pear.php.net',
    'pear.unl.edu',
    'pear2.php.net',
    'pecl.php.net',
), $chans, 'listChannels');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===