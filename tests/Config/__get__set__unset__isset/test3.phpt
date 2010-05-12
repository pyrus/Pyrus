--TEST--
\PEAR2\Pyrus\Config::__isset() and friends
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
set_include_path(''); // disable include_path cascading for simplicity
$a = $configclass::singleton($testpath, $testpath . '/blah');
$a->addConfigValue('foo', 'booya');
$a->addConfigValue('foo2', 'booya2', false);

foreach (array('registry',
                                        'channelregistry',
                                        'systemvars',
                                        'uservars',
                                        'mainsystemvars',
                                        'mainuservars',
                                        'userfile',
                                        'path',
                                        'php_dir',
                                        'data_dir') as $var) {
    $test->assertTrue(isset($a->$var), $var . ' isset');
}

foreach (array('registry',
                                        'channelregistry',
                                        'systemvars',
                                        'uservars',
                                        'mainsystemvars',
                                        'mainuservars',
                                        'userfile',
                                        'path',) as $var) {
    try {
        unset($a->$var);
        throw new Exception($var . ' unsetting did not fail');
    } catch (\PEAR2\Pyrus\Config\Exception $e) {
        $test->assertEquals('Cannot unset magic value ' . $var, $e->getMessage(), $var . ' message');
    }
    $test->assertTrue(isset($a->$var), $var . ' isset');
}

foreach (array('php_dir', 'data_dir') as $var) {
    try {
        unset($a->$var);
        throw new Exception($var . ' unsetting did not fail');
    } catch (\PEAR2\Pyrus\Config\Exception $e) {
        $test->assertEquals('Cannot unset ' . $var, $e->getMessage(), $var . ' message');
    }
}


$test->assertFalse(isset($a->test_dir), 'test_dir 1');
$a->test_dir = 'hi';
$test->assertTrue(isset($a->test_dir), 'test_dir 2');
unset($a->test_dir);
$test->assertFalse(isset($a->test_dir), 'test_dir 3');

$test->assertFalse(isset($a->foo), 'foo 1');
$a->foo = 'hi';
$test->assertTrue(isset($a->foo), 'foo 2');
unset($a->foo);
$test->assertFalse(isset($a->foo), 'foo 3');

$test->assertFalse(isset($a->default_channel), 'default_channel 1');
$a->default_channel = 'pear.php.net';
$test->assertTrue(isset($a->default_channel), 'default_channel 2');
unset($a->default_channel);
$test->assertFalse(isset($a->default_channel), 'default_channel 3');

$test->assertFalse(isset($a->foo2), 'foo 1');
$a->foo2 = 'hi';
$test->assertTrue(isset($a->foo2), 'foo 2');
unset($a->foo2);
$test->assertFalse(isset($a->foo2), 'foo 3');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
