--TEST--
\Pyrus\Channel::__construct() test basics for ssl channels
--FILE--
<?php
$thrown = false;
require __DIR__ . '/setup.php.inc';
try {
    $chan = new \Pyrus\ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_ssl_channel.xml');
    try {
        $chan->validate();
    } catch (Exception $e) {
        $thrown = true;
        $test->assertEquals(false, $thrown, 'validate channel file '.$e->getMessage());
    }
    $test->assertEquals(true, $chan->ssl, 'getSSL');
    $test->assertEquals(443, $chan->port, 'getPort');
} catch(Exception $e) {
    $thrown = true;
}
$test->assertEquals(false, $thrown, 'parsed channel.xml file fine, no exceptions thrown');



?>
===DONE===
--EXPECT--
===DONE===