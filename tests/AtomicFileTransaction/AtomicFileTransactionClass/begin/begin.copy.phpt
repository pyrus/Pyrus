--TEST--
\Pyrus\AtomicFileTransaction::begin() with copy to journal directory
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
mkdir(TESTDIR . '/php');
touch(TESTDIR . '/php/foo', 1234567);
touch(TESTDIR . '/php/another');
umask(0);
mkdir(TESTDIR . '/php/sub/deep/deep/thing', 0777, true);
mkdir(TESTDIR . '/php/anothernew/dir', 0777, true);
umask(022);
touch(TESTDIR . '/php/anothernew/dir/file');

$test->assertFileExists(TESTDIR . '/php', TESTDIR . '/php');
$test->assertFileExists(TESTDIR . '/php/foo', TESTDIR . '/php/foo');
$test->assertFileExists(TESTDIR . '/php/another', TESTDIR . '/php/another');
$test->assertFileExists(TESTDIR . '/php/sub/deep/deep/thing', TESTDIR . '/php/sub/deep/deep/thing');
$test->assertFileExists(TESTDIR . '/php/anothernew/dir', TESTDIR . '/php/another/dir');
$test->assertFileExists(TESTDIR . '/php/anothernew/dir/file', TESTDIR . '/php/another/dir/file');

$test->assertFileNotExists(TESTDIR . '/.journal-php', TESTDIR . '/.journal-php before');
$test->assertFileNotExists(TESTDIR . '/.journal-php/foo', TESTDIR . '/.journal-php/foo before');
$test->assertFileNotExists(TESTDIR . '/.journal-php/another', TESTDIR . '/.journal-php/another before');
$test->assertFileNotExists(TESTDIR . '/.journal-php/sub/deep/deep/thing',
                           TESTDIR . '/.journal-php/sub/deep/deep/thing before');
$test->assertFileNotExists(TESTDIR . '/.journal-php/anothernew/dir',
                           TESTDIR . '/.journal-php/another/dir before');
$test->assertFileNotExists(TESTDIR . '/.journal-php/anothernew/dir/file',
                           TESTDIR . '/.journal-php/another/dir/file before');

$role = new \Pyrus\Installer\Role\Php(\Pyrus\Config::current(),
                                            \Pyrus\Installer\Role::getInfo('php'));
$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject($role);

\Pyrus\AtomicFileTransaction::begin();

$test->assertFileExists(TESTDIR . '/php', TESTDIR . '/php after');
$test->assertFileExists(TESTDIR . '/php/foo', TESTDIR . '/php/foo after');
$test->assertFileExists(TESTDIR . '/php/another', TESTDIR . '/php/another after');
$test->assertFileExists(TESTDIR . '/php/sub/deep/deep/thing', TESTDIR . '/php/sub/deep/deep/thing after');
$test->assertFileExists(TESTDIR . '/php/anothernew/dir', TESTDIR . '/php/another/dir after');
$test->assertFileExists(TESTDIR . '/php/anothernew/dir/file',
                        TESTDIR . '/php/another/dir/file after');

$test->assertFileExists(TESTDIR . '/.journal-php', TESTDIR . '/.journal-php after');
$test->assertFileExists(TESTDIR . '/.journal-php/foo', TESTDIR . '/.journal-php/foo after');
$test->assertFileExists(TESTDIR . '/.journal-php/another', TESTDIR . '/.journal-php/another after');
$test->assertFileExists(TESTDIR . '/.journal-php/sub/deep/deep/thing',
                        TESTDIR . '/.journal-php/sub/deep/deep/thing after');
$test->assertFileExists(TESTDIR . '/.journal-php/anothernew/dir',
                        TESTDIR . '/.journal-php/another/dir after');
$test->assertFileExists(TESTDIR . '/.journal-php/anothernew/dir/file',
                        TESTDIR . '/.journal-php/another/dir/file after');

$test->assertEquals(decoct(0777), decoct(0777 & fileperms(TESTDIR . '/.journal-php/sub/deep/deep/thing')),
                    'perms ' . TESTDIR . '/.journal-php/sub/deep/deep/thing');
$test->assertEquals(decoct(0755), decoct(0755 & fileperms(TESTDIR . '/.journal-php/anothernew')),
                    'perms ' . TESTDIR . '/.journal-php/anothernew');
$test->assertEquals(decoct(0777), decoct(0777 & fileperms(TESTDIR . '/.journal-php/anothernew/dir')),
                    'perms ' . TESTDIR . '/.journal-php/anothernew/dir');

$test->assertEquals(filemtime(TESTDIR . '/php/another'),
                    filemtime(TESTDIR . '/.journal-php/another'), 'mtime 1');
$test->assertEquals(1234567, filemtime(TESTDIR . '/.journal-php/foo'), 'foo mtime');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===