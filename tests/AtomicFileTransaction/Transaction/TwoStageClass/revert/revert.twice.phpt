--TEST--
\Pyrus\AtomicFileTransaction::commit()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

// This code is checked by revert.phpt
mkdir($path . '');
touch($path . '/foo', 1234567);
touch($path . '/another');
umask(0);
mkdir($path . '/sub/deep/deep/thing', 0777, true);
mkdir($path . '/anothernew/dir', 0777, true);
umask(022);
touch($path . '/anothernew/dir/file');

$instance->begin();

unlink($journalPath . DIRECTORY_SEPARATOR . 'foo');
file_put_contents($journalPath . DIRECTORY_SEPARATOR . 'newfile', 'hithere');

$instance->commit();

$instance->revert();
// end code checked in revert.phpt

try {
$instance->revert();
} catch (RuntimeException $e) {
    $test->assertEquals('Cannot restore backup, no backup directory available.', $e->getMessage(), 'restore backup twice');
}

$test->assertFileNotExists($backupPath, $backupPath . ' after commit');
$test->assertFileNotExists($backupPath . '/foo', $backupPath . '/foo after commit');
$test->assertFileNotExists($backupPath . '/newfile', $backupPath . '/newfile after commit');
$test->assertFileNotExists($backupPath . '/another', $backupPath . '/another after commit');
$test->assertFileNotExists($backupPath . '/sub/deep/deep/thing', $backupPath . '/sub/deep/deep/thing after commit');
$test->assertFileNotExists($backupPath . '/anothernew/dir', $backupPath . '/another/dir after commit');
$test->assertFileNotExists($backupPath . '/anothernew/dir/file', $backupPath . '/another/dir/file after commit');

$test->assertFileExists($path, $path . ' after');
$test->assertFileExists($path . '/foo', $path . '/foo after');
$test->assertFileExists($path . '/another', $path . '/another after');
$test->assertFileExists($path . '/sub/deep/deep/thing', $path . '/sub/deep/deep/thing after');
$test->assertFileExists($path . '/anothernew/dir', $path . '/another/dir after');
$test->assertFileExists($path . '/anothernew/dir/file', $path . '/another/dir/file after');

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