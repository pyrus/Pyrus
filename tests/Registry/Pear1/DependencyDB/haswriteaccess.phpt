--TEST--
Pear1 registry dependency database: hasWriteAccess()
--SKIPIF--
<?php
if (substr(PHP_OS, 0, 3) === 'WIN') {
    die('skip chmod is not fully supported on windows');
}
?>
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
mkdir($dir . DIRECTORY_SEPARATOR . 'oops');
chmod($dir, 0);
$db = new Pyrus\Registry\Pear1\DependencyDB($dir . DIRECTORY_SEPARATOR . 'oops');
$test->assertEquals(false, $db->hasWriteAccess(), 'basic test');
$db = new Pyrus\Registry\Pear1\DependencyDB('cbcb$^$^#(#(');
$test->assertEquals(false, $db->hasWriteAccess(), 'basic test');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===