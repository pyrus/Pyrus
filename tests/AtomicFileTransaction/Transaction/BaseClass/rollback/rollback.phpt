--TEST--
\Pyrus\AtomicFileTransaction::rollback()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
mkdir($path);
touch($path . '/foo');
touch($path . '/another');
mkdir($path . '/sub/deep/deep/thing', 0777, true);
mkdir($path . '/anothernew/dir', 0777, true);
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

$test->assertTrue($instance->inTransaction(), 'after rollback not in transaction');

$instance->rollback();

$test->assertFileExists($path, $path . ' after');
$test->assertFileExists($path . '/foo', $path . '/foo after');
$test->assertFileExists($path . '/another', $path . '/another after');
$test->assertFileExists($path . '/sub/deep/deep/thing', $path . '/sub/deep/deep/thing after');
$test->assertFileExists($path . '/anothernew/dir', $path . '/another/dir after');
$test->assertFileExists($path . '/anothernew/dir/file', $path . '/another/dir/file after');

$test->assertFileNotExists($journalPath, $journalPath . ' after');
$test->assertFileNotExists($journalPath . '/foo', $journalPath . '/foo after');
$test->assertFileNotExists($journalPath . '/another', $journalPath . '/another after');
$test->assertFileNotExists($journalPath . '/sub/deep/deep/thing', $journalPath . '/sub/deep/deep/thing after');
$test->assertFileNotExists($journalPath . '/anothernew/dir', $journalPath . '/another/dir after');
$test->assertFileNotExists($journalPath . '/anothernew/dir/file', $journalPath . '/another/dir/file after');

$test->assertFalse($instance->inTransaction(), 'after rollback not in transaction');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===