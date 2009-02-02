--TEST--
PEAR2_Pyrus_Config::userInitialized() basic test
--INI--
extension_dir=
--ENV--
PATH=.
PHP_PEAR_BIN_DIR=
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$test->assertFalse(r::userInitialized(), 'first, nothing found');
r::$foo = 2;
$test->assertTrue(r::userInitialized(), 'second, myfile.xml found');
r::$foo = 1;
copy(__DIR__ . '/myfile.xml', __DIR__ . '/pearconfig.xml');
$dir = getcwd();
chdir(__DIR__);
$test->assertFileExists(__DIR__ . '/pearconfig.xml', 'pearconfig.xml does not exist');
$test->assertTrue(r::userInitialized(), 'third, pearconfig.xml found');
unlink(__DIR__ . '/pearconfig.xml');
chdir($dir);
?>
===DONE===
--EXPECT--
===DONE===
