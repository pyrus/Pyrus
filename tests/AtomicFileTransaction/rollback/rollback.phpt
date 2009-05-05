--TEST--
PEAR2_Pyrus_AtomicFileTransaction::rollback()
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';
mkdir(__DIR__ . '/testit/src');
touch(__DIR__ . '/testit/src/foo');
touch(__DIR__ . '/testit/src/another');
mkdir(__DIR__ . '/testit/src/sub/deep/deep/thing', 0777, true);
mkdir(__DIR__ . '/testit/src/anothernew/dir', 0777, true);
touch(__DIR__ . '/testit/src/anothernew/dir/file');

$test->assertFileExists(__DIR__ . '/testit/src', __DIR__ . '/testit/src');
$test->assertFileExists(__DIR__ . '/testit/src/foo', __DIR__ . '/testit/src/foo');
$test->assertFileExists(__DIR__ . '/testit/src/another', __DIR__ . '/testit/src/another');
$test->assertFileExists(__DIR__ . '/testit/src/sub/deep/deep/thing', __DIR__ . '/testit/src/sub/deep/deep/thing');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir', __DIR__ . '/testit/src/another/dir');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir/file', __DIR__ . '/testit/src/another/dir/file');

$test->assertFileNotExists(__DIR__ . '/testit/.journal-src', __DIR__ . '/testit/.journal-src before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/foo', __DIR__ . '/testit/.journal-src/foo before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/another', __DIR__ . '/testit/.journal-src/another before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/sub/deep/deep/thing', __DIR__ . '/testit/.journal-src/sub/deep/deep/thing before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/anothernew/dir', __DIR__ . '/testit/.journal-src/another/dir before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/anothernew/dir/file', __DIR__ . '/testit/.journal-src/another/dir/file before');

$atomic = PEAR2_Pyrus_AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

PEAR2_Pyrus_AtomicFileTransaction::begin();
PEAR2_Pyrus_AtomicFileTransaction::rollback();

$test->assertFileExists(__DIR__ . '/testit/src', __DIR__ . '/testit/src after');
$test->assertFileExists(__DIR__ . '/testit/src/foo', __DIR__ . '/testit/src/foo after');
$test->assertFileExists(__DIR__ . '/testit/src/another', __DIR__ . '/testit/src/another after');
$test->assertFileExists(__DIR__ . '/testit/src/sub/deep/deep/thing', __DIR__ . '/testit/src/sub/deep/deep/thing after');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir', __DIR__ . '/testit/src/another/dir after');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir/file', __DIR__ . '/testit/src/another/dir/file after');

$test->assertFileNotExists(__DIR__ . '/testit/.journal-src', __DIR__ . '/testit/.journal-src after');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/foo', __DIR__ . '/testit/.journal-src/foo after');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/another', __DIR__ . '/testit/.journal-src/another after');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/sub/deep/deep/thing', __DIR__ . '/testit/.journal-src/sub/deep/deep/thing after');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/anothernew/dir', __DIR__ . '/testit/.journal-src/another/dir after');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/anothernew/dir/file', __DIR__ . '/testit/.journal-src/another/dir/file after');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===