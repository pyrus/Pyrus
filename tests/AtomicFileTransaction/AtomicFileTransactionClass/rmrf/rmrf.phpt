--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::rmrf()
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';
touch(TESTDIR . '/foo');
touch(TESTDIR . '/another');
mkdir(TESTDIR . '/sub/deep/deep/thing', 0777, true);
mkdir(TESTDIR . '/anothernew/dir', 0777, true);
touch(TESTDIR . '/anothernew/dir/file');

$test->assertFileExists(TESTDIR, TESTDIR);
$test->assertFileExists(TESTDIR . '/foo', TESTDIR . '/foo');
$test->assertFileExists(TESTDIR . '/another', TESTDIR . '/another');
$test->assertFileExists(TESTDIR . '/sub/deep/deep/thing', TESTDIR . '/sub/deep/deep/thing');
$test->assertFileExists(TESTDIR . '/anothernew/dir', TESTDIR . '/another/dir');
$test->assertFileExists(TESTDIR . '/anothernew/dir/file', TESTDIR . '/another/dir/file');

try {
    \PEAR2\Pyrus\Filesystem::rmrf(TESTDIR, true);
    throw new Exception('did not fail and should');
} catch (RuntimeException $e) {
    $test->assertEquals('Unable to fully remove ' . TESTDIR .', directory is not empty',
                        $e->getMessage(), 'removal message');
}

\PEAR2\Pyrus\Filesystem::rmrf(TESTDIR);

$test->assertFileNotExists(TESTDIR, TESTDIR);
$test->assertFileNotExists(TESTDIR . '/foo', TESTDIR . '/foo');
$test->assertFileNotExists(TESTDIR . '/another', TESTDIR . '/another');
$test->assertFileNotExists(TESTDIR . '/sub/deep/deep/thing', TESTDIR . '/sub/deep/deep/thing');
$test->assertFileNotExists(TESTDIR . '/anothernew/dir', TESTDIR . '/another/dir');
$test->assertFileNotExists(TESTDIR . '/anothernew/dir/file', TESTDIR . '/another/dir/file');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===