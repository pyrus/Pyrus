--TEST--
\PEAR2\Pyrus\Channel\Mirror set
--FILE--
<?php
$thrown = false;
require dirname(__FILE__) . '/setup.php.inc';

$fake = new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml');
$fake->mirror['ugly.pear.php.net']->ssl = true;
$fake->mirror['ugly.pear.php.net']->port = 5;
$channel = new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml');

// test setting to null
$test->assertEquals(true, isset($channel->mirror['us.pear.php.net']), 'before');
$channel->mirror['us.pear.php.net'] = null;
$test->assertEquals(false, isset($channel->mirror['us.pear.php.net']), 'after');

try {
    $channel->mirror['us.pear.php.net'] = 'hi';
    throw new Exception('setting to hi should have failed and did not');
} catch (\PEAR2\Pyrus\ChannelFile\Exception $e) {
    $test->assertEquals('Can only set mirror to a ' .
                        '\PEAR2\Pyrus\ChannelFile\v1\Mirror object', $e->getMessage(), 'error');
}

$channel->mirror['us.pear.php.net'] = $fake->mirror['ugly.pear.php.net'];
$test->assertEquals(5, $channel->mirror['us.pear.php.net']->port, 'verify setting worked');

// test setting existing mirror

$fake->mirror['ugly.pear.php.net']->port = 82;

$channel->mirror['us.pear.php.net'] = $fake->mirror['ugly.pear.php.net'];
$test->assertEquals(82, $channel->mirror['us.pear.php.net']->port, 'verify 2');

unset($channel->mirror['us.pear.php.net']);
unset($channel->mirror['de.pear.php.net']);
unset($channel->mirror['us.pear.php.net']);
$test->assertEquals(0, count($channel->mirrors), 'after all removed');
?>
===DONE===
--EXPECT--
===DONE===