--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::commit()
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';
mkdir(__DIR__ . '/testit/src');
touch(__DIR__ . '/testit/src/foo', 1234567);
touch(__DIR__ . '/testit/src/another');
umask(0);
mkdir(__DIR__ . '/testit/src/sub/deep/deep/thing', 0777, true);
mkdir(__DIR__ . '/testit/src/anothernew/dir', 0777, true);
umask(022);
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

\PEAR2\Pyrus\AtomicFileTransaction::begin();

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

$test->assertFileExists(__DIR__ . '/testit/src', __DIR__ . '/testit/src after');
$test->assertFileExists(__DIR__ . '/testit/src/foo', __DIR__ . '/testit/src/foo after');
$test->assertFileExists(__DIR__ . '/testit/src/another', __DIR__ . '/testit/src/another after');
$test->assertFileExists(__DIR__ . '/testit/src/sub/deep/deep/thing', __DIR__ . '/testit/src/sub/deep/deep/thing after');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir', __DIR__ . '/testit/src/another/dir after');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir/file', __DIR__ . '/testit/src/another/dir/file after');

$test->assertFileExists(__DIR__ . '/testit/.journal-src', __DIR__ . '/testit/.journal-src after');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/foo', __DIR__ . '/testit/.journal-src/foo after');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/newfile', __DIR__ . '/testit/.journal-src/newfile before');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/another', __DIR__ . '/testit/.journal-src/another after');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/sub/deep/deep/thing', __DIR__ . '/testit/.journal-src/sub/deep/deep/thing after');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/anothernew/dir', __DIR__ . '/testit/.journal-src/another/dir after');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/anothernew/dir/file', __DIR__ . '/testit/.journal-src/another/dir/file after');

$test->assertEquals(decoct(0777), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-src/sub/deep/deep/thing')), 'perms ' . __DIR__ . '/testit/.journal-src/sub/deep/deep/thing');
$test->assertEquals(decoct(0755), decoct(0755 & fileperms(__DIR__ . '/testit/.journal-src/anothernew')), 'perms ' . __DIR__ . '/testit/.journal-src/anothernew');
$test->assertEquals(decoct(0777), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-src/anothernew/dir')), 'perms ' . __DIR__ . '/testit/.journal-src/anothernew/dir');

$test->assertEquals(filemtime(__DIR__ . '/testit/src/another'), filemtime(__DIR__ . '/testit/.journal-src/another'), 'mtime 1');
$test->assertEquals(1234567, filemtime(__DIR__ . '/testit/.journal-src/foo'), 'foo mtime');

$atomic->removePath('foo');
$atomic->createOrOpenPath('newfile', 'hithere');

$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/foo', __DIR__ . '/testit/.journal-src/foo after erase');
$test->assertFileExists(__DIR__ . '/testit/.journal-src/newfile', __DIR__ . '/testit/.journal-src/newfile after create');

$test->assertFileExists(__DIR__ . '/testit/src/foo', __DIR__ . '/testit/.journal-src/foo after erase 2');
$test->assertFileNotExists(__DIR__ . '/testit/src/newfile', __DIR__ . '/testit/.journal-src/newfile after create 2');

\PEAR2\Pyrus\AtomicFileTransaction::commit();

$test->assertFileExists(__DIR__ . '/testit/src', __DIR__ . '/testit/src after commit');
$test->assertFileNotExists(__DIR__ . '/testit/src/foo', __DIR__ . '/testit/src/foo after commit');
$test->assertFileExists(__DIR__ . '/testit/src/newfile', __DIR__ . '/testit/src/newfile after commit');
$test->assertFileExists(__DIR__ . '/testit/src/another', __DIR__ . '/testit/src/another after commit');
$test->assertFileExists(__DIR__ . '/testit/src/sub/deep/deep/thing', __DIR__ . '/testit/src/sub/deep/deep/thing after commit');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir', __DIR__ . '/testit/src/another/dir after commit');
$test->assertFileExists(__DIR__ . '/testit/src/anothernew/dir/file', __DIR__ . '/testit/src/another/dir/file after commit');

$test->assertFileExists(__DIR__ . '/testit/.old-src', __DIR__ . '/testit/.old-src after commit');
$test->assertFileExists(__DIR__ . '/testit/.old-src/foo', __DIR__ . '/testit/.old-src/foo after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src/newfile', __DIR__ . '/testit/.old-src/newfile after commit');
$test->assertFileExists(__DIR__ . '/testit/.old-src/another', __DIR__ . '/testit/.old-src/another after commit');
$test->assertFileExists(__DIR__ . '/testit/.old-src/sub/deep/deep/thing', __DIR__ . '/testit/.old-src/sub/deep/deep/thing after commit');
$test->assertFileExists(__DIR__ . '/testit/.old-src/anothernew/dir', __DIR__ . '/testit/.old-src/another/dir after commit');
$test->assertFileExists(__DIR__ . '/testit/.old-src/anothernew/dir/file', __DIR__ . '/testit/.old-src/another/dir/file after commit');

$test->assertFileNotExists(__DIR__ . '/testit/.journal-src', __DIR__ . '/testit/.journal-src after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/foo', __DIR__ . '/testit/.journal-src/foo after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/newfile', __DIR__ . '/testit/.journal-src/newfile after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/another', __DIR__ . '/testit/.journal-src/another after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/sub/deep/deep/thing', __DIR__ . '/testit/.journal-src/sub/deep/deep/thing after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/anothernew/dir', __DIR__ . '/testit/.journal-src/another/dir after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/anothernew/dir/file', __DIR__ . '/testit/.journal-src/another/dir/file after commit');

\PEAR2\Pyrus\AtomicFileTransaction::removeBackups();

$test->assertFileNotExists(__DIR__ . '/testit/.old-src', __DIR__ . '/testit/.old-src after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src/foo', __DIR__ . '/testit/.old-src/foo after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src/newfile', __DIR__ . '/testit/.old-src/newfile after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src/another', __DIR__ . '/testit/.old-src/another after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src/sub/deep/deep/thing', __DIR__ . '/testit/.old-src/sub/deep/deep/thing after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src/anothernew/dir', __DIR__ . '/testit/.old-src/another/dir after commit');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src/anothernew/dir/file', __DIR__ . '/testit/.old-src/another/dir/file after commit');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===