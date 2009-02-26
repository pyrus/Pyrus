--TEST--
PEAR2_Pyrus_Channel::__construct() basic test
--FILE--
<?php
$thrown = false;
require dirname(__FILE__) . '/setup.php.inc';
try {
    $channel = new PEAR2_Pyrus_Channel(file_get_contents(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml'));
    try {
        $channel->validate();
    } catch (Exception $e) {
        $thrown = true;
        $test->assertEquals(false, $thrown, 'validate channel file '.$e->getMessage());
    }
    $test->assertEquals(true, $channel->toChannelObject() instanceof PEAR2_Pyrus_Channel, 'toChannelObject');
    $test->assertEquals('pear.php.net', $channel->server, 'getChannel');
    $test->assertEquals('PHP Extension and Application Repository', $channel->summary, 'getSummary');
    $test->assertEquals('pear', $channel->alias, 'getAlias');
    $test->assertEquals(false, $channel->ssl, 'getSSL');
    $test->assertEquals(80, $channel->port, 'getSSL');
    
    $restbase = 'http://pear.php.net/';
    require __DIR__ . '/rest.template';
    
    $mirrors = $channel->mirrors;
    $test->assertEquals(true, is_array($mirrors), 'Mirrors returns array');
    $test->assertEquals(2, count($mirrors), 'Two mirrors returned');
    $test->assertEquals(true, $mirrors['us.pear.php.net'] instanceof PEAR2_Pyrus_Channel_Mirror, 'Mirror returned is PEAR2_Pyrus_Channel_Mirror object');
    $test->assertEquals(true, $mirrors['de.pear.php.net'] instanceof PEAR2_Pyrus_Channel_Mirror, 'Mirror returned is PEAR2_Pyrus_Channel_Mirror object');
    
    $test->assertEquals(array('attribs'=>array(
                                'version' => 'default'),
                                '_content'=>'PEAR2_Pyrus_Validate'), $channel->getValidationPackage(), 'getValidationPackage');
    
    $test->assertEquals(true, $channel->getValidationObject() instanceof PEAR2_Pyrus_Validate, 'getValidationObject');
    
    // should be moved to a separate test?
    $test->assertEquals(true, $channel->validChannelServer('__uri'), 'validChannelServer(__uri)');
    $test->assertEquals(true, $channel->validChannelServer('pear.php.net'), 'validChannelServer(pear.php.net)');
    
} catch(Exception $e) {
    $thrown = true;
}
$test->assertEquals(false, $thrown, 'parsed channel.xml file fine, no exceptions thrown');



?>
===DONE===
--EXPECT--
===DONE===