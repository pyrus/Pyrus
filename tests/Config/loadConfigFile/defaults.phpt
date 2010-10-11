--TEST--
\PEAR2\Pyrus\Config::loadConfigFile() no configuration found, use defaults
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
set_include_path($testpath); // disable include_path cascading for simplicity
tc::$test = $test;
$a = new tc($testpath, $testpath . '/notfound', 1);
//restore_include_path(); done in tc->setCascadingRegistries
$test->assertEquals($testpath, $a->pearDir, 'peardir');
$test->assertEquals($testpath . '/notfound', $a->userFile, 'userfile');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
