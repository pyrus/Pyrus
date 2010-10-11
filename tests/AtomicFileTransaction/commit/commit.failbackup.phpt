--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::commit() failure, can't create backup dir
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';
mkdir(TESTDIR . '/src');
touch(TESTDIR . '/src/foo', 1234567);
touch(TESTDIR . '/src/another');
umask(0);
mkdir(TESTDIR . '/src/sub/deep/deep/thing', 0777, true);
mkdir(TESTDIR . '/src/anothernew/dir', 0777, true);
umask(022);
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

$atomic->begin();

$test->assertFileExists(TESTDIR . '/src', TESTDIR . '/src after');
$test->assertFileExists(TESTDIR . '/src/foo', TESTDIR . '/src/foo after');
$test->assertFileExists(TESTDIR . '/src/another', TESTDIR . '/src/another after');
$test->assertFileExists(TESTDIR . '/src/sub/deep/deep/thing', TESTDIR . '/src/sub/deep/deep/thing after');
$test->assertFileExists(TESTDIR . '/src/anothernew/dir', TESTDIR . '/src/another/dir after');
$test->assertFileExists(TESTDIR . '/src/anothernew/dir/file', TESTDIR . '/src/another/dir/file after');

$test->assertFileExists(TESTDIR . '/.journal-src', TESTDIR . '/.journal-src after');
$test->assertFileExists(TESTDIR . '/.journal-src/foo', TESTDIR . '/.journal-src/foo after');
$test->assertFileNotExists(TESTDIR . '/.journal-src/newfile', TESTDIR . '/.journal-src/newfile before');
$test->assertFileExists(TESTDIR . '/.journal-src/another', TESTDIR . '/.journal-src/another after');
$test->assertFileExists(TESTDIR . '/.journal-src/sub/deep/deep/thing', TESTDIR . '/.journal-src/sub/deep/deep/thing after');
$test->assertFileExists(TESTDIR . '/.journal-src/anothernew/dir', TESTDIR . '/.journal-src/another/dir after');
$test->assertFileExists(TESTDIR . '/.journal-src/anothernew/dir/file', TESTDIR . '/.journal-src/another/dir/file after');

$test->assertEquals(decoct(0777), decoct(0777 & fileperms(TESTDIR . '/.journal-src/sub/deep/deep/thing')), 'perms ' . TESTDIR . '/.journal-src/sub/deep/deep/thing');
$test->assertEquals(decoct(0755), decoct(0755 & fileperms(TESTDIR . '/.journal-src/anothernew')), 'perms ' . TESTDIR . '/.journal-src/anothernew');
$test->assertEquals(decoct(0777), decoct(0777 & fileperms(TESTDIR . '/.journal-src/anothernew/dir')), 'perms ' . TESTDIR . '/.journal-src/anothernew/dir');

$test->assertEquals(filemtime(TESTDIR . '/src/another'), filemtime(TESTDIR . '/.journal-src/another'), 'mtime 1');
$test->assertEquals(1234567, filemtime(TESTDIR . '/.journal-src/foo'), 'foo mtime');

$atomic->removePath('foo');
$atomic->createOrOpenPath('newfile', 'hithere');

$test->assertFileNotExists(TESTDIR . '/.journal-src/foo', TESTDIR . '/.journal-src/foo after erase');
$test->assertFileExists(TESTDIR . '/.journal-src/newfile', TESTDIR . '/.journal-src/newfile after create');

$test->assertFileExists(TESTDIR . '/src/foo', TESTDIR . '/.journal-src/foo after erase 2');
$test->assertFileNotExists(TESTDIR . '/src/newfile', TESTDIR . '/.journal-src/newfile after create 2');

mkdir(TESTDIR . '/.old-src');
try {
    \PEAR2\Pyrus\AtomicFileTransaction::commit();
    throw new Exception('Expected exception.');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $cause = array();
    $e->getCauseMessage($cause);
    $test->assertEquals('CRITICAL - unable to complete transaction, rename of actual to backup path failed', $cause[1]['message'], 'error');
    $test->assertEquals('ERROR: commit failed', $e->getMessage(), 'error');
}
$test->assertFileNotExists(TESTDIR . '/.journal-src', 'verify transaction cancelled');
$test->assertFileExists(TESTDIR . '/src', 'verify transaction cancelled and source not deleted');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===