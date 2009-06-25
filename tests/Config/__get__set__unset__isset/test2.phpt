--TEST--
PEAR2_Pyrus_Config::__set() and friends
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
                                        'path') as $var) {
    try {
        $a->$var = 'oops';
        throw new Exception($var . ' setting did not fail');
    } catch (PEAR2_Pyrus_Config_Exception $e) {
        $test->assertEquals('Cannot set magic configuration variable ' . $var, $e->getMessage(), $var . ' message');
    }
}
try {
    $a->php_dir = 'oops';
    throw new Exception('php_dir setting did not fail');
} catch (PEAR2_Pyrus_Config_Exception $e) {
    $test->assertEquals('Cannot set php_dir, move the repository to change this value', $e->getMessage(), 'php_dir message');
}
try {
    $a->data_dir = 'oops';
    throw new Exception('data_dir setting did not fail');
} catch (PEAR2_Pyrus_Config_Exception $e) {
    $test->assertEquals('Cannot set data_dir, move the repository to change this value', $e->getMessage(), 'data_dir message');
}
try {
    $a->gronk = 'oops';
    throw new Exception('gronk setting did not fail');
} catch (PEAR2_Pyrus_Config_Exception $e) {
    $test->assertEquals('Unknown configuration variable "gronk" in location ' . $testpath, $e->getMessage(), 'gronk message');
}
$a->test_dir = 'hi';
$test->assertEquals('hi', $a->test_dir, 'test_dir');
$a->foo = 'hi2';
$test->assertEquals('hi2', $a->foo, 'foo');
$a->foo2 = 'hi3';
$test->assertEquals('hi3', $a->foo2, 'foo2');

// test setting channel-specific variables
$test->assertEquals('', $a->openssl_cert, 'cert before');
$a->openssl_cert = 'hi';
$test->assertEquals('hi', $a->openssl_cert, 'cert after');
$a->default_channel = 'pecl.php.net';
$test->assertEquals('', $a->openssl_cert, 'cert pecl before');
$a->default_channel = 'pear2.php.net';
$test->assertEquals('hi', $a->openssl_cert, 'cert pear2 after');
$a->default_channel = 'pecl.php.net';
$test->assertEquals('', $a->openssl_cert, 'cert pecl before bye');
$a->openssl_cert = 'bye';
$test->assertEquals('bye', $a->openssl_cert, 'cert pecl after bye');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
