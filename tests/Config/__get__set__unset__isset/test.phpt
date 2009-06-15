--TEST--
PEAR2_Pyrus_Config::__get() and friends
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$unused = $configclass::singleton($testpath . '/booya', $testpath . '/blah');

$a = $configclass::singleton($testpath . PATH_SEPARATOR . $testpath . DIRECTORY_SEPARATOR .
                             'booya', $testpath . '/blah');
$a->addConfigValue('foo', 'booya');
$a->addConfigValue('foo2', 'booya2', false);

$test->assertEquals('pear2.php.net', $a->default_channel, 'get user config var');
$test->assertEquals('booya', $a->foo, 'get custom system config var');
$test->assertEquals('booya2', $a->foo2, 'get custom user config var');
$test->assertEquals('PEAR2_Pyrus_Registry', get_class($a->registry), 'registry');
$test->assertEquals('PEAR2_Pyrus_ChannelRegistry', get_class($a->channelregistry), 'channelregistry');
$test->assertEquals(array_merge($a->mainsystemvars, array('foo')), $a->systemvars, 'systemvars');
$test->assertEquals(array_merge($a->mainuservars, array('foo2'), $a->mainchannelvars), $a->uservars, 'uservars');
$test->assertEquals($testpath, $a->location, 'location');
$test->assertEquals($testpath . PATH_SEPARATOR . $testpath . DIRECTORY_SEPARATOR .
                             'booya', $a->path, 'path');
$test->assertEquals($testpath . '/blah', $a->userfile, 'userfile');
$test->assertEquals(array('foo'), $a->customsystemvars, 'customsystemvars');
$test->assertEquals(array('foo2'), $a->customuservars, 'customuservars');

$a->foo2 = 'hi';
$test->assertEquals('hi', $a->foo2, 'test retrieving set value');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
