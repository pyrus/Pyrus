--TEST--
PEAR2_Pyrus_AtomicFileTransaction::begin(), journal dir exists
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

mkdir(__DIR__ . '/testit/.journal-src');

$role = new PEAR2_Pyrus_Installer_Role_Php(PEAR2_Pyrus_Config::current());
$atomic = new PEAR2_Pyrus_AtomicFileTransaction($role, __DIR__ . '/testit/src');

$test->assertFileNotExists(__DIR__ . '/testit/src/', 'before');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/', 'before');

$atomic->begin();

$test->assertFileNotExists(__DIR__ . '/testit/src/', 'after');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/', 'after');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===