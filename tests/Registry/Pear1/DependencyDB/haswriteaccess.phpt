--TEST--
Pear1 registry dependency database: hasWriteAccess()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
mkdir($dir . DIRECTORY_SEPARATOR . 'oops');
chmod($dir, 0);
$db = new PEAR2\Pyrus\Registry\Pear1\DependencyDB($dir . DIRECTORY_SEPARATOR . 'oops');
$test->assertEquals(false, $db->hasWriteAccess(), 'basic test');
$db = new PEAR2\Pyrus\Registry\Pear1\DependencyDB('cbcb$^$^#(#(');
$test->assertEquals(false, $db->hasWriteAccess(), 'basic test');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===