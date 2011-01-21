--TEST--
\PEAR2\Pyrus\ChannelRegistry::listChannels() default channels
--FILE--
<?php
require dirname(__DIR__) . '/../setup.php.inc';
$c = getTestConfig();

$chans = $c->channelregistry->listChannels();
sort($chans);

$test->assertEquals(array(
    '__uri',
    'doc.php.net',
    'pear.php.net',
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