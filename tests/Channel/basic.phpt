--TEST--
PEAR2_Pyrus_Channel::__construct() basic test
--FILE--
<?php
$thrown = false;
require dirname(__FILE__) . '/setup.php.inc';
try {
    $chan = new PEAR2_Pyrus_Channel(file_get_contents(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml'));
    try {
        $chan->validate();
    } catch (Exception $e) {
        $thrown = true;
        $test->assertEquals(false, $thrown, 'validate channel file '.$e->getMessage());
    }
    $test->assertEquals('pear.php.net', $chan->server, 'getChannel');
    $test->assertEquals('PHP Extension and Application Repository', $chan->summary, 'getSummary');
    $test->assertEquals('pear', $chan->alias, 'getAlias');
    $test->assertEquals(false, $chan->ssl, 'getSSL');
    $test->assertEquals(80, (int)$chan->port, 'getSSL');
    $test->assertEquals(true, $chan->supportsREST(), 'supportsREST');
    
    $exp_rest = array();
    $exp_rest[] = array (
            'attribs'  => array ('type' => 'REST1.0'),
            '_content' => 'http://pear.php.net/rest1.0/');
    $exp_rest[] = array (
            'attribs'  => array ('type' => 'REST1.1'),
            '_content' => 'http://pear.php.net/rest1.1/');
    $exp_rest[] = array (
            'attribs'  => array ('type' => 'REST1.2'),
            '_content' => 'http://pear.php.net/rest1.2/');
    $exp_rest[] = array (
            'attribs'  => array ('type' => 'REST1.3'),
            '_content' => 'http://pear.php.net/rest1.3/');
    
    $rest = $chan->getREST();
    $test->assertEquals(true, is_array($rest), 'array of REST dirs returned');
    $test->assertEquals($exp_rest, $rest, 'Rest servers returned');
    $test->assertEquals('http://pear.php.net/rest1.0/', $chan->getBaseURL('REST1.0'), 'REST 1.0');
    $test->assertEquals('http://pear.php.net/rest1.1/', $chan->getBaseURL('REST1.1'), 'REST 1.1');
    $test->assertEquals('http://pear.php.net/rest1.2/', $chan->getBaseURL('REST1.2'), 'REST 1.2');
    $test->assertEquals('http://pear.php.net/rest1.3/', $chan->getBaseURL('REST1.3'), 'REST 1.3');
    $test->assertEquals($exp_rest, $chan->getFunctions('rest'), 'getFunctions');
    
    $mirrors = $chan->mirrors;
    $test->assertEquals(true, is_array($mirrors), 'Mirrors returns array');
    $test->assertEquals(2, count($mirrors), 'Two mirrors returned');
    $test->assertEquals(true, $mirrors['us.pear.php.net'] instanceof PEAR2_Pyrus_Channel_Mirror, 'Mirror returned is PEAR2_Pyrus_Channel_Mirror object');
    $test->assertEquals(true, $mirrors['de.pear.php.net'] instanceof PEAR2_Pyrus_Channel_Mirror, 'Mirror returned is PEAR2_Pyrus_Channel_Mirror object');
    
    $test->assertEquals(array('attribs'=>array(
                                'version' => 'default'),
                                '_content'=>'PEAR2_Pyrus_Validate'), $chan->getValidationPackage(), 'getValidationPackage');
    
    $test->assertEquals(true, $chan->getValidationObject() instanceof PEAR2_Pyrus_Validate, 'getValidationObject');
    
    // should be moved to a separate test?
    $test->assertEquals(true, $chan->validChannelServer('__uri'), 'validChannelServer(__uri)');
    $test->assertEquals(true, $chan->validChannelServer('pear.php.net'), 'validChannelServer(pear.php.net)');
    
} catch(Exception $e) {
    $thrown = true;
}
$test->assertEquals(false, $thrown, 'parsed channel.xml file fine, no exceptions thrown');



?>
===DONE===
--EXPECT--
===DONE===