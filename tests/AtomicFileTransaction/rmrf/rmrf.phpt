--TEST--
PEAR2_Pyrus_AtomicFileTransaction::rmrf()
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';
touch(__DIR__ . '/testit/foo');
touch(__DIR__ . '/testit/another');
mkdir(__DIR__ . '/testit/sub/deep/deep/thing', 0777, true);
mkdir(__DIR__ . '/testit/anothernew/dir', 0777, true);
touch(__DIR__ . '/testit/anothernew/dir/file');

$test->assertFileExists(__DIR__ . '/testit', __DIR__ . '/testit');
$test->assertFileExists(__DIR__ . '/testit/foo', __DIR__ . '/testit/foo');
$test->assertFileExists(__DIR__ . '/testit/another', __DIR__ . '/testit/another');
$test->assertFileExists(__DIR__ . '/testit/sub/deep/deep/thing', __DIR__ . '/testit/sub/deep/deep/thing');
$test->assertFileExists(__DIR__ . '/testit/anothernew/dir', __DIR__ . '/testit/another/dir');
$test->assertFileExists(__DIR__ . '/testit/anothernew/dir/file', __DIR__ . '/testit/another/dir/file');

$role = new PEAR2_Pyrus_Installer_Role_Php(PEAR2_Pyrus_Config::current());
$atomic = new PEAR2_Pyrus_AtomicFileTransaction($role, __DIR__ . '/testit/src');

$atomic->rmrf(__DIR__ . '/testit');

$test->assertFileNotExists(__DIR__ . '/testit', __DIR__ . '/testit');
$test->assertFileNotExists(__DIR__ . '/testit/foo', __DIR__ . '/testit/foo');
$test->assertFileNotExists(__DIR__ . '/testit/another', __DIR__ . '/testit/another');
$test->assertFileNotExists(__DIR__ . '/testit/sub/deep/deep/thing', __DIR__ . '/testit/sub/deep/deep/thing');
$test->assertFileNotExists(__DIR__ . '/testit/anothernew/dir', __DIR__ . '/testit/another/dir');
$test->assertFileNotExists(__DIR__ . '/testit/anothernew/dir/file', __DIR__ . '/testit/another/dir/file');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===