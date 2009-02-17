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

$channel = new PEAR2_Pyrus_Channel($channel_array);

$test->assertEquals(false, $channel->addMirror('foo.example.com'), 'URI channel cannot have mirrors');
$test->assertEquals(false, $channel->getFunctions('rest'), 'getFunctions returns false for __uri');
$channel->resetREST();
$test->assertEquals(false, $channel->getBaseURL('REST1.0'), 'BaseURL does not exists for __uri');


?>
===DONE===
--EXPECT--
===DONE===