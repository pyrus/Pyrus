--TEST--
\PEAR2\Pyrus\Channel\Mirror unset
--FILE--
<?php
$thrown = false;
require __DIR__ . '/setup.php.inc';

$channel = new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml');
$test->assertEquals(true, isset($channel->mirror['us.pear.php.net']), 'before');
unset($channel->mirror['us.pear.php.net']);
$test->assertEquals(false, isset($channel->mirror['us.pear.php.net']), 'after');
?>
===DONE===
--EXPECT--
===DONE===