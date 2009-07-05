--TEST--
\pear2\Pyrus\Channel Create new channel
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$channel_array = array(
                    'attribs' => array('version'=>'1.0',
                                       'xmlns'=>'http://pear.php.net/channel-1.0',
                                       'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                                       'xsi:schemaLocation' => 'http://pear.php.net/channel-1.0 
http://pear.php.net/dtd/channel-1.0.xsd'),
                    'name'    => 'foo.example.com',
                    'summary' => 'bar');
$channelinfo = new \pear2\Pyrus\ChannelFile\v1;
$channelinfo->fromArray($channel_array);

try {
    $channel = new \pear2\Pyrus\Channel($channelinfo);
    $channel->validate();
    throw new Exception('Was able to create channel with no server details.');
} catch (Exception $e) {
    $test->assertEquals('Invalid channel.xml', $e->getMessage(), 'Invalid channel array');
}

$channel_array['servers'] = array('primary'=>array('rest'=>array('baseurl'=>array('attribs'=>array('type'=>'REST1.0'),
                                                                                  'http://foo.example.com/rest/'))));

$channelinfo = new \pear2\Pyrus\ChannelFile\v1;
$channelinfo->fromArray($channel_array);
$channel = new \pear2\Pyrus\Channel($channelinfo);

$test->assertEquals($channel_array, $channel->getArray(), 'getArray');

$channel->name = 'pear.example.com';
$test->assertEquals('pear.example.com', $channel->name, 'getName');

$channel->summary = 'Test channel summary';
$test->assertEquals('Test channel summary', $channel->summary, 'getSummary');
try {
    $channel->summary = '';
    throw new Exception('Was able to set empty summary');
} catch(Exception $e) {
    $test->assertEquals('Channel summary cannot be empty', $e->getMessage(), 'Channel summary cannot be empty');
}

$test->assertEquals('pear.example.com', $channel->alias, 'Alias defaults to channel name');
$channel->alias = 'myalias';
$test->assertEquals('myalias', $channel->alias, 'setAlias');

$test->assertEquals(false, $channel->ssl, 'SSL defaults to false');
$channel->ssl = true;
$test->assertEquals(true, $channel->ssl, 'setSSL(true)');
$test->assertEquals(443, $channel->port, 'When SSL is set, port defaults to 443');
$channel->ssl = false;
$test->assertEquals(false, $channel->ssl, 'setSSL(false)');

$test->assertEquals(80, $channel->port, 'Port defaults to 80');
$channel->port = 1337;
$test->assertEquals(1337, $channel->port, 'set/getPort');

$test->assertIsa('\pear2\Pyrus\ChannelFile\v1\Servers', $channel->mirrors, 'Wrong mirrors class');
$test->assertEquals(0, count($channel->mirrors), 'only primary server');

try {
    $channel->name = '';
    throw new Exception('Was able to set channel to empty name');
} catch(Exception $e) {
    $test->assertEquals('Primary server must be non-empty', $e->getMessage(), 'Primary server must be non-empty');
}

try {
    $channel->name = '?limmozeen';
    throw new Exception('Was able to set channel to invalid name');
} catch(Exception $e) {
    $test->assertEquals('Primary server "?limmozeen" is not a valid channel server', $e->getMessage(), 'Setting invalid channel name');
}

try {
    $channel->alias = '?limmozeen';
    throw new Exception('Was able to set channel alias to invalid name');
} catch(Exception $e) {
    $test->assertEquals('Alias "?limmozeen" is not a valid channel alias', $e->getMessage(), 'Setting invalid channel alias');
}

$test->assertEquals(true, ($channel->lastModified()>=time()), 'Last modified date is current time by default');

require __DIR__ . '/rest_creation.template';

$test->assertIsa('\pear2\Pyrus\ChannelFile\v1\Mirror', $channel->mirrors['pear.mirror.com'], 'Mirror was set');
$test->assertIsa('\pear2\Pyrus\ChannelFile\v1\Mirror', $channel->mirrors['pear.mirror2.com'], 'Mirror #2 was set');
$test->assertIsa('\pear2\Pyrus\ChannelFile\v1\Mirror', $channel->mirrors['pear.mirror.com'], 'Mirror #1 still exists');

$channel->mirrors['pear.mirror3.com']->port = 999;
$test->assertEquals(999, $channel->mirrors['pear.mirror3.com']->port, 'Mirror #3 added with specific port number');


?>
===DONE===
--EXPECT--
===DONE===