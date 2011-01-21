--TEST--
\PEAR2\Pyrus\Channel\Mirror basic tests
--FILE--
<?php
$thrown = false;
require __DIR__ . '/setup.php.inc';

$channel = new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml');

$test->assertEquals(false, isset($channel->mirror['ugly.pear.php.net']), 'test offsetExists false');
$test->assertEquals('pear.php.net', $channel->mirrors['ugly.pear.php.net']->channel, 'getChannel ugly');

$test->assertEquals(true, isset($channel->mirror['us.pear.php.net']), 'test offsetExists');
$test->assertEquals('pear.php.net', $channel->mirrors['us.pear.php.net']->channel, 'getChannel');
$test->assertEquals('us.pear.php.net', $channel->mirrors['us.pear.php.net']->name, 'getName');
$test->assertEquals(80, $channel->mirrors['us.pear.php.net']->port, 'getPort (default)');
$test->assertEquals(false, $channel->mirrors['us.pear.php.net']->ssl, 'getSSL (default)');
$channel->mirrors['us.pear.php.net']->setSSL(true);
$test->assertEquals(true, $channel->mirrors['us.pear.php.net']->ssl, 'set and get SSL(true)');
$test->assertEquals(443, $channel->mirrors['us.pear.php.net']->port, 'getPort (ssl default)');
$channel->mirrors['us.pear.php.net']->setSSL(false);
$test->assertEquals(false, $channel->mirrors['us.pear.php.net']->ssl, 'set and get SSL(false)');
$test->assertEquals(80, $channel->mirrors['us.pear.php.net']->port, 'getPort (reset to 80)');
$test->assertEquals(3452, $channel->mirrors['de.pear.php.net']->port, 'getPort (non-standard)');
$channel->mirrors['de.pear.php.net']->setPort(999);
$test->assertEquals(999, $channel->mirrors['de.pear.php.net']->port, 'setPort');

$test->assertEquals(true, $channel->mirrors['de.pear.php.net']->ssl, 'getSSL (non-standard port)');
$test->assertEquals(true, $channel->mirrors['us.pear.php.net']->supportsREST(), 'supportsREST');

try {
    $channel->mirrors['de.pear.php.net']->setName('');
    throw new Exception('Was able to set mirror host to empty name');
} catch(Exception $e) {
    $test->assertEquals('Mirror server must be non-empty', $e->getMessage(), 'Mirror server must be non-empty');
}

try {
    $channel->mirrors['de.pear.php.net']->setName('?limmozeen');
    throw new Exception('Was able to set mirror host to invalid name');
} catch(Exception $e) {
    $test->assertEquals('Mirror server "?limmozeen" for channel "pear.php.net" is not a valid channel server', $e->getMessage(), 'Setting invalid mirror name');
}

$channel->mirrors['de.pear.php.net']->setName('pear.php.net.de');
$test->assertEquals('pear.php.net.de', $channel->mirrors['pear.php.net.de']->name, 'setName');

$channel = $channel->mirrors['us.pear.php.net'];

$restbase = 'http://us.pear.php.net/';
require __DIR__ . '/rest.template';

require __DIR__ . '/rest_creation.template';
?>
===DONE===
--EXPECT--
===DONE===