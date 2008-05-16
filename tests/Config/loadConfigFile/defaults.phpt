--TEST--
PEAR2_Pyrus_Config::loadConfigFile() no configuration found, use defaults
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
set_include_path(''); // disable include_path cascading for simplicity
$a = new tc($testpath, $testpath . '/notfound');
$test->assertEquals($testpath, $a->pearDir, 'peardir');
$test->assertEquals($testpath . '/notfound', $a->userFile, 'userfile');
?>
===DONE===
--CLEAN--
<?php rmdir(__DIR__ . '/testpath'); ?>
--EXPECT--
===DONE===
