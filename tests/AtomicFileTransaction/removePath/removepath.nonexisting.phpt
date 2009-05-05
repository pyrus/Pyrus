--TEST--
PEAR2_Pyrus_AtomicFileTransaction::removePath(), path doesn't exist
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$role = new PEAR2_Pyrus_Installer_Role_Php(PEAR2_Pyrus_Config::current());
$atomic = new PEAR2_Pyrus_AtomicFileTransaction($role, __DIR__ . '/testit/src');

$atomic->begin();

$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/foo', 'before');
$atomic->removePath('foo');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/foo', 'should still exist');

$atomic->rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===