--TEST--
PEAR2_Pyrus_Channel_Mirror set
--FILE--
<?php
$thrown = false;
require dirname(__FILE__) . '/setup.php.inc';

$fake = new PEAR2_Pyrus_ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml');
$fake->mirror['ugly.pear.php.net']->ssl = true;
$fake->mirror['ugly.pear.php.net']->port = 5;
$channel = new PEAR2_Pyrus_ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml');

// test setting to null
$test->assertEquals(true, isset($channel->mirror['us.pear.php.net']), 'before');
$channel->mirror['us.pear.php.net'] = null;
$test->assertEquals(false, isset($channel->mirror['us.pear.php.net']), 'after');

try {
    $channel->mirror['us.pear.php.net'] = 'hi';
    throw new Exception('setting to hi should have failed and did not');
} catch (PEAR2_Pyrus_ChannelFile_Exception $e) {
    $test->assertEquals('Can only set mirror to a ' .
                        'PEAR2_Pyrus_ChannelFile_v1_Mirror object', $e->getMessage(), 'error');
}

$channel->mirror['us.pear.php.net'] = $fake->mirror['ugly.pear.php.net'];

$test->assertEquals(5, $channel->mirror['us.pear.php.net']->port, 'verify setting worked');
?>
===DONE===
--EXPECT--
===DONE===