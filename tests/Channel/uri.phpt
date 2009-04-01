--TEST--
PEAR2_Pyrus_Channel URI Channel tests
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$channel_array = array(
                    'attribs' => array('version'=>'1.0', 'xmlns'=>'http://pear.php.net/channel-1.0'),
                    'name'    => '__uri',
                    'summary' => 'URI Channel');
$channel_array['servers'] = array('primary'=>array('rest'=>array('baseurl'=>array('attribs'=>array('type'=>'REST1.0'),
                                                                                  'http://foo.example.com/rest/'))));

$channelinfo = new PEAR2_Pyrus_ChannelFile_v1;
$channelinfo->fromArray($channel_array);
$channel = new PEAR2_Pyrus_Channel($channelinfo);

$test->assertEquals(false, $channel->addMirror('foo.example.com'), 'URI channel cannot have mirrors');
try {
    $test->assertEquals(false, $channel->protocols, 'getFunctions returns false for __uri');
} catch (PEAR2_Pyrus_Channel_Exception $e) {
    $test->assertEquals('__uri pseudo-channel has no protocols', $e->getMessage(), 'rest message');
}

?>
===DONE===
--EXPECT--
===DONE===