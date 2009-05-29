--TEST--
PEAR2_Pyrus_Config::loadConfigFile() corrupt userfile
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
set_include_path($testpath); // disable include_path cascading for simplicity
file_put_contents($testpath . '/blah', '<?xml version="1.0" ?>oops> <cra&p>; ?>');
try {
    tc::$test = $test;
    $a = new tc($testpath, $testpath . '/blah');
    //restore_include_path(); done in tc->setCascadingRegistries
    $test->assertEquals($testpath, $a->pearDir, 'peardir');
    $test->assertEquals($testpath . '/blah', $a->userFile, 'userfile');
} catch (Exception $e) {
    $test->assertException($e, 'PEAR2_Pyrus_Config_Exception', 'Unable to parse invalid user PEAR configuration at "' . $testpath . '/blah"', 'exception');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
