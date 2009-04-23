--TEST--
PEAR2_Pyrus_ChannelRegistry_Xml::listChannels() default channels
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$chans = $creg->listChannels();
sort($chans);

$test->assertEquals(array(
    '__uri',
    'pear.php.net',
    'pear2.php.net',
    'pecl.php.net',
), $chans, 'listChannels');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===