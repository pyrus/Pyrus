--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::commit()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
mkdir($path);
touch($path . '/foo', 1234567);
touch($path . '/another');
umask(0);
mkdir($path . '/sub/deep/deep/thing', 0777, true);
mkdir($path . '/anothernew/dir', 0777, true);
umask(022);
touch($path . '/anothernew/dir/file');

$test->assertFileExists($path, $path);
$test->assertFileExists($path . '/foo', $path . '/foo');
$test->assertFileExists($path . '/another', $path . '/another');
$test->assertFileExists($path . '/sub/deep/deep/thing', $path . '/sub/deep/deep/thing');
$test->assertFileExists($path . '/anothernew/dir', $path . '/another/dir');
$test->assertFileExists($path . '/anothernew/dir/file', $path . '/another/dir/file');

$test->assertFileNotExists($journalPath, $journalPath . ' before');
$test->assertFileNotExists($journalPath . '/foo', $journalPath . '/foo before');
$test->assertFileNotExists($journalPath . '/another', $journalPath . '/another before');
$test->assertFileNotExists($journalPath . '/sub/deep/deep/thing', $journalPath . '/sub/deep/deep/thing before');
$test->assertFileNotExists($journalPath . '/anothernew/dir', $journalPath . '/another/dir before');
$test->assertFileNotExists($journalPath . '/anothernew/dir/file', $journalPath . '/another/dir/file before');

$instance->begin();

$test->assertFileExists($path, $path . ' after');
$test->assertFileExists($path . '/foo', $path . '/foo after');
$test->assertFileExists($path . '/another', $path . '/another after');
$test->assertFileExists($path . '/sub/deep/deep/thing', $path . '/sub/deep/deep/thing after');
$test->assertFileExists($path . '/anothernew/dir', $path . '/another/dir after');
$test->assertFileExists($path . '/anothernew/dir/file', $path . '/another/dir/file after');

$test->assertFileExists($journalPath, $journalPath . ' after');
$test->assertFileExists($journalPath . '/foo', $journalPath . '/foo after');
$test->assertFileNotExists($journalPath . '/newfile', $journalPath . '/newfile before');
$test->assertFileExists($journalPath . '/another', $journalPath . '/another after');
$test->assertFileExists($journalPath . '/sub/deep/deep/thing', $journalPath . '/sub/deep/deep/thing after');
$test->assertFileExists($journalPath . '/anothernew/dir', $journalPath . '/another/dir after');
$test->assertFileExists($journalPath . '/anothernew/dir/file', $journalPath . '/another/dir/file after');

$test->assertEquals(decoct(0777), decoct(0777 & fileperms($journalPath . '/sub/deep/deep/thing')), 'perms ' . $journalPath . '/sub/deep/deep/thing');
$test->assertEquals(decoct(0755), decoct(0755 & fileperms($journalPath . '/anothernew')), 'perms ' . $journalPath . '/anothernew');
$test->assertEquals(decoct(0777), decoct(0777 & fileperms($journalPath . '/anothernew/dir')), 'perms ' . $journalPath . '/anothernew/dir');

$test->assertEquals(filemtime($path . '/another'), filemtime($journalPath . '/another'), 'mtime 1');
$test->assertEquals(1234567, filemtime($journalPath . '/foo'), 'foo mtime');

unlink($journalPath . DIRECTORY_SEPARATOR . 'foo');
file_put_contents($journalPath . DIRECTORY_SEPARATOR . 'newfile', 'hithere');

$test->assertFileNotExists($journalPath . '/foo', $journalPath . '/foo after erase');
$test->assertFileExists($journalPath . '/newfile', $journalPath . '/newfile after create');

$test->assertFileExists($path . '/foo', $journalPath . '/foo after erase 2');
$test->assertFileNotExists($path . '/newfile', $journalPath . '/newfile after create 2');

$instance->commit();

$test->assertFileExists($path, $path . ' after commit');
$test->assertFileNotExists($path . '/foo', $path . '/foo after commit');
$test->assertFileExists($path . '/newfile', $path . '/newfile after commit');
$test->assertFileExists($path . '/another', $path . '/another after commit');
$test->assertFileExists($path . '/sub/deep/deep/thing', $path . '/sub/deep/deep/thing after commit');
$test->assertFileExists($path . '/anothernew/dir', $path . '/another/dir after commit');
$test->assertFileExists($path . '/anothernew/dir/file', $path . '/another/dir/file after commit');

$test->assertFileNotExists($journalPath, $journalPath . ' after commit');
$test->assertFileNotExists($journalPath . '/foo', $journalPath . '/foo after commit');
$test->assertFileNotExists($journalPath . '/newfile', $journalPath . '/newfile after commit');
$test->assertFileNotExists($journalPath . '/another', $journalPath . '/another after commit');
$test->assertFileNotExists($journalPath . '/sub/deep/deep/thing', $journalPath . '/sub/deep/deep/thing after commit');
$test->assertFileNotExists($journalPath . '/anothernew/dir', $journalPath . '/another/dir after commit');
$test->assertFileNotExists($journalPath . '/anothernew/dir/file', $journalPath . '/another/dir/file after commit');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===