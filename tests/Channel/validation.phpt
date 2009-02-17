--TEST--
PEAR2_Pyrus_Channel Validation tests
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
$test->assertEquals(array('attribs' => array('version' => 'default'),
                          '_content' => 'PEAR2_Pyrus_Validate'), $channel->getValidationPackage(), 'Get default validation package');

$channel->setValidationPackage('PEAR2_Pyrus_Validator_PECL', '1.0');

$test->assertEquals(array('_content' => 'PEAR2_Pyrus_Validator_PECL',
                          'attribs' => array('version' => '1.0')), $channel->getValidationPackage(), 'Set custom validation package');

$test->assertEquals(true, $channel->getValidationObject() instanceof PEAR2_Pyrus_Validator_PECL, 'getValidationObject returns what is set');
$test->assertEquals(true, $channel->getValidationObject('PEAR2_Pyrus_Validator_PECL') instanceof PEAR2_Pyrus_Validate, 'getValidationObject Channel validation packages are all checked by PEAR2_Pyrus_Validate');

$channel->setValidationPackage('PEAR2_Bogus_Validation', '6.0');
$test->assertEquals(false, $channel->getValidationObject(), 'getValidationObject returns false for validtion packages that cannot be loaded');

$channel->setValidationPackage(null, '1.0');
$test->assertEquals(true, $channel->getValidationObject() instanceof PEAR2_Pyrus_Validate, 'setValidationPackage resets to default');


?>
===DONE===
--EXPECT--
===DONE===