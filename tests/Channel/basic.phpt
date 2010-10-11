--TEST--
\PEAR2\Pyrus\Channel::__construct() basic test
--FILE--
<?php
$thrown = false;
require __DIR__ . '/setup.php.inc';
try {
    $channel = new \PEAR2\Pyrus\ChannelFile(dirname(__DIR__).'/ChannelRegistry/sample_channel_complex.xml');
    try {
        $channel->validate();
    } catch (Exception $e) {
        $thrown = true;
        $test->assertEquals(false, $thrown, 'validate channel file '.$e->getMessage());
    }
    $test->assertEquals('pear.php.net', $channel->name, 'getChannel');
    $test->assertEquals('PHP Extension and Application Repository', $channel->summary, 'getSummary');
    $test->assertEquals('pear', $channel->alias, 'getAlias');
    $test->assertEquals('pear', $channel->suggestedalias, 'getSuggestedAlias');
    $test->assertEquals(false, $channel->ssl, 'getSSL');
    $test->assertEquals(80, $channel->port, 'getPort');

    $restbase = 'http://pear.php.net/';
    require __DIR__ . '/rest.template';

    try {
        $a = $channel->protocols->poop['oops'];
        throw new Exception('poop protocol should not work and did');
    } catch (\PEAR2\Pyrus\ChannelFile\Exception $e) {
        $test->assertEquals('Unknown protocol: poop', $e->getMessage(), 'error');
    }

    try {
        $channel->protocols->poop = 'oops';
        throw new Exception('poop protocol set should not work and did');
    } catch (\PEAR2\Pyrus\ChannelFile\Exception $e) {
        $test->assertEquals('Unknown protocol: poop', $e->getMessage(), 'error set');
    }

    $mirrors = $channel->mirrors;
    $test->assertIsa('\PEAR2\Pyrus\ChannelFile\v1\Servers', $mirrors, 'Mirrors returns object');
    $test->assertEquals(2, count($mirrors), 'Two mirrors returned');
    $test->assertIsa('\PEAR2\Pyrus\ChannelFile\v1\Mirror', $mirrors['us.pear.php.net'], 'Mirror returned is \PEAR2\Pyrus\ChannelFile\v1\Mirror object');
    $test->assertIsa('\PEAR2\Pyrus\ChannelFile\v1\Mirror', $mirrors['de.pear.php.net'], 'Mirror returned is \PEAR2\Pyrus\ChannelFile\v1\Mirror object');

    $test->assertEquals(array('attribs'=>array(
                                'version' => 'default'),
                                '_content'=>'PEAR_Validate'), $channel->getValidationPackage(), 'getValidationPackage');

    $test->assertEquals(true, $channel->getValidationObject() instanceof \PEAR2\Pyrus\Validate, 'getValidationObject');

    // should be moved to a separate test?
    $test->assertEquals(true, $channel->validChannelServer('__uri'), 'validChannelServer(__uri)');
    $test->assertEquals(true, $channel->validChannelServer('pear.php.net'), 'validChannelServer(pear.php.net)');

} catch(Exception $e) {
    echo $e;
    $thrown = true;
}
$test->assertEquals(false, $thrown, 'parsed channel.xml file fine, no exceptions thrown');



?>
===DONE===
--EXPECT--
===DONE===