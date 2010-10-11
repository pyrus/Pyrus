--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::rollback() 2
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';
mkdir(TESTDIR . '/src');
touch(TESTDIR . '/src/foo');
touch(TESTDIR . '/src/another');
mkdir(TESTDIR . '/src/sub/deep/deep/thing', 0777, true);
mkdir(TESTDIR . '/src/anothernew/dir', 0777, true);
touch(TESTDIR . '/src/anothernew/dir/file');

$test->assertFileExists(TESTDIR . '/src', TESTDIR . '/src');
$test->assertFileExists(TESTDIR . '/src/foo', TESTDIR . '/src/foo');
$test->assertFileExists(TESTDIR . '/src/another', TESTDIR . '/src/another');
$test->assertFileExists(TESTDIR . '/src/sub/deep/deep/thing', TESTDIR . '/src/sub/deep/deep/thing');
$test->assertFileExists(TESTDIR . '/src/anothernew/dir', TESTDIR . '/src/another/dir');
$test->assertFileExists(TESTDIR . '/src/anothernew/dir/file', TESTDIR . '/src/another/dir/file');

$test->assertFileNotExists(TESTDIR . '/.journal-src', TESTDIR . '/.journal-src before');
$test->assertFileNotExists(TESTDIR . '/.journal-src/foo', TESTDIR . '/.journal-src/foo before');
$test->assertFileNotExists(TESTDIR . '/.journal-src/another', TESTDIR . '/.journal-src/another before');
$test->assertFileNotExists(TESTDIR . '/.journal-src/sub/deep/deep/thing', TESTDIR . '/.journal-src/sub/deep/deep/thing before');
$test->assertFileNotExists(TESTDIR . '/.journal-src/anothernew/dir', TESTDIR . '/.journal-src/another/dir before');
$test->assertFileNotExists(TESTDIR . '/.journal-src/anothernew/dir/file', TESTDIR . '/.journal-src/another/dir/file before');

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();
\PEAR2\Pyrus\AtomicFileTransaction::commit();
\PEAR2\Pyrus\AtomicFileTransaction::rollback();

$test->assertFileExists(TESTDIR . '/src', TESTDIR . '/src after');
$test->assertFileExists(TESTDIR . '/src/foo', TESTDIR . '/src/foo after');
$test->assertFileExists(TESTDIR . '/src/another', TESTDIR . '/src/another after');
$test->assertFileExists(TESTDIR . '/src/sub/deep/deep/thing', TESTDIR . '/src/sub/deep/deep/thing after');
$test->assertFileExists(TESTDIR . '/src/anothernew/dir', TESTDIR . '/src/another/dir after');
$test->assertFileExists(TESTDIR . '/src/anothernew/dir/file', TESTDIR . '/src/another/dir/file after');

$test->assertFileNotExists(TESTDIR . '/.journal-src', TESTDIR . '/.journal-src after');
$test->assertFileNotExists(TESTDIR . '/.journal-src/foo', TESTDIR . '/.journal-src/foo after');
$test->assertFileNotExists(TESTDIR . '/.journal-src/another', TESTDIR . '/.journal-src/another after');
$test->assertFileNotExists(TESTDIR . '/.journal-src/sub/deep/deep/thing', TESTDIR . '/.journal-src/sub/deep/deep/thing after');
$test->assertFileNotExists(TESTDIR . '/.journal-src/anothernew/dir', TESTDIR . '/.journal-src/another/dir after');
$test->assertFileNotExists(TESTDIR . '/.journal-src/anothernew/dir/file', TESTDIR . '/.journal-src/another/dir/file after');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===