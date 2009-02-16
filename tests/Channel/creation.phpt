--TEST--
PEAR2_Pyrus_Channel Create new channel
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$channel_array = array(
                    'attribs' => array('version'=>'1.0', 'xmlns'=>'http://pear.php.net/channel-1.0'),
                    'name'    => 'foo.example.com',
                    'summary' => 'bar');

try {
    $channel = new PEAR2_Pyrus_Channel($channel_array);
    throw new Exception('Was able to create channel with no server details.');
} catch (Exception $e) {
    $test->assertEquals('Invalid channel.xml', $e->getMessage(), 'Invalid channel array');
}

$channel_array['servers'] = array('primary'=>array('rest'=>array('baseurl'=>array('attribs'=>array('type'=>'REST1.0'),
                                                                                  'http://foo.example.com/rest/'))));

$channel = new PEAR2_Pyrus_Channel($channel_array);

$channel->name = 'pear.example.com';
$test->assertEquals('pear.example.com', $channel->server, 'getChannel');

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

$test->assertEquals(false, $channel->getSSL(), 'SSL defaults to false');
$channel->ssl = true;
$test->assertEquals(true, $channel->getSSL(), 'setSSL(true)');
$test->assertEquals(443, $channel->port, 'When SSL is set, port defaults to 443');
$channel->ssl = false;
$test->assertEquals(false, $channel->getSSL(), 'setSSL(false)');

$test->assertEquals(80, $channel->port, 'Port defaults to 80');
$channel->port = 1337;
$test->assertEquals(1337, $channel->port, 'set/getPort');

$test->assertEquals(true, is_array($channel->mirrors), 'Empty mirrors array');
$test->assertEquals(0, count($channel->mirrors), 'Empty mirrors array');

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

$channel->resetREST();

$channel->setBaseURL('REST1.0', 'http://pear.example.com/rest1.0/');
$exp_rest = array (
            'attribs'  => array ('type' => 'REST1.0'),
            '_content' => 'http://pear.example.com/rest1.0/');
$test->assertEquals($exp_rest, $channel->getREST(), 'setBaseURL');

$channel->setBaseURL('REST1.1', 'http://pear.example.com/rest1.1/');

$exp_rest = array($exp_rest);
$exp_rest[] = array (
            'attribs'  => array ('type' => 'REST1.1'),
            '_content' => 'http://pear.example.com/rest1.1/');
$test->assertEquals($exp_rest, $channel->getREST(), 'setBaseURL #2 adding second baseurl');

$channel->setBaseURL('REST1.0', 'http://pear.example.com/rest1.00/');
$exp_rest[0]['_content'] = 'http://pear.example.com/rest1.00/';
$test->assertEquals($exp_rest, $channel->getREST(), 'setBaseURL #3 Update URL of existing baseurl');

$channel->addMirror('pear.mirror.com');
$test->assertEquals(true, $channel->mirrors['pear.mirror.com'] instanceof PEAR2_Pyrus_Channel_Mirror, 'Mirror was set');

$channel->addMirror('pear.mirror2.com');
$test->assertEquals(true, $channel->mirrors['pear.mirror2.com'] instanceof PEAR2_Pyrus_Channel_Mirror, 'Mirror #2 was set');
$test->assertEquals(true, $channel->mirrors['pear.mirror.com'] instanceof PEAR2_Pyrus_Channel_Mirror, 'Mirror #1 still exists');

try {
    $channel->getFunctions('llama');
    throw new Exception('Was able to get the llama functions');
} catch(Exception $e) {
    $test->assertEquals('Unknown protocol: llama', $e->getMessage(), 'Get invalid protocol functions');
}

?>
===DONE===
--EXPECT--
===DONE===