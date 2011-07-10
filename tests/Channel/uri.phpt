--TEST--
\Pyrus\Channel URI Channel tests
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$channel_array = array(
                    'attribs' => array('version'=>'1.0', 'xmlns'=>'http://pear.php.net/channel-1.0'),
                    'name'    => '__uri',
                    'summary' => 'URI Channel');
$channel_array['servers'] = array('primary'=>array('rest'=>array('baseurl'=>array('attribs'=>array('type'=>'REST1.0'),
                                                                                  'http://foo.example.com/rest/'))));

$channelinfo = new \Pyrus\ChannelFile\v1;
$channelinfo->fromArray($channel_array);
$channel = new \Pyrus\Channel($channelinfo);

try {
    $channel->mirrors['foo.example.com'];
    throw new Exception('should not have worked');
} catch (\Pyrus\Channel\Exception $e) {
    $test->assertEquals('__uri pseudo-channel cannot have mirrors', $e->getMessage(), 'rest message');
}
try {
    $test->assertEquals(false, $channel->protocols, 'getFunctions returns false for __uri');
} catch (\Pyrus\Channel\Exception $e) {
    $test->assertEquals('__uri pseudo-channel has no protocols', $e->getMessage(), 'rest message');
}

?>
===DONE===
--EXPECT--
===DONE===