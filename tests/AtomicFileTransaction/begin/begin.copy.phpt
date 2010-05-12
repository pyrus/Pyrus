--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::begin() with copy to journal directory
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.php.inc';
mkdir(__DIR__ . '/testit/php');
touch(__DIR__ . '/testit/php/foo', 1234567);
touch(__DIR__ . '/testit/php/another');
umask(0);
mkdir(__DIR__ . '/testit/php/sub/deep/deep/thing', 0777, true);
mkdir(__DIR__ . '/testit/php/anothernew/dir', 0777, true);
umask(022);
touch(__DIR__ . '/testit/php/anothernew/dir/file');

$test->assertFileExists(__DIR__ . '/testit/php', __DIR__ . '/testit/php');
$test->assertFileExists(__DIR__ . '/testit/php/foo', __DIR__ . '/testit/php/foo');
$test->assertFileExists(__DIR__ . '/testit/php/another', __DIR__ . '/testit/php/another');
$test->assertFileExists(__DIR__ . '/testit/php/sub/deep/deep/thing', __DIR__ . '/testit/php/sub/deep/deep/thing');
$test->assertFileExists(__DIR__ . '/testit/php/anothernew/dir', __DIR__ . '/testit/php/another/dir');
$test->assertFileExists(__DIR__ . '/testit/php/anothernew/dir/file', __DIR__ . '/testit/php/another/dir/file');

$test->assertFileNotExists(__DIR__ . '/testit/.journal-php', __DIR__ . '/testit/.journal-php before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-php/foo', __DIR__ . '/testit/.journal-php/foo before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-php/another', __DIR__ . '/testit/.journal-php/another before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-php/sub/deep/deep/thing',
                           __DIR__ . '/testit/.journal-php/sub/deep/deep/thing before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-php/anothernew/dir',
                           __DIR__ . '/testit/.journal-php/another/dir before');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-php/anothernew/dir/file',
                           __DIR__ . '/testit/.journal-php/another/dir/file before');

$role = new \PEAR2\Pyrus\Installer\Role\Php(\PEAR2\Pyrus\Config::current(),
                                            \PEAR2\Pyrus\Installer\Role::getInfo('php'));
$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject($role);

\PEAR2\Pyrus\AtomicFileTransaction::begin();

$test->assertFileExists(__DIR__ . '/testit/php', __DIR__ . '/testit/php after');
$test->assertFileExists(__DIR__ . '/testit/php/foo', __DIR__ . '/testit/php/foo after');
$test->assertFileExists(__DIR__ . '/testit/php/another', __DIR__ . '/testit/php/another after');
$test->assertFileExists(__DIR__ . '/testit/php/sub/deep/deep/thing', __DIR__ . '/testit/php/sub/deep/deep/thing after');
$test->assertFileExists(__DIR__ . '/testit/php/anothernew/dir', __DIR__ . '/testit/php/another/dir after');
$test->assertFileExists(__DIR__ . '/testit/php/anothernew/dir/file',
                        __DIR__ . '/testit/php/another/dir/file after');

$test->assertFileExists(__DIR__ . '/testit/.journal-php', __DIR__ . '/testit/.journal-php after');
$test->assertFileExists(__DIR__ . '/testit/.journal-php/foo', __DIR__ . '/testit/.journal-php/foo after');
$test->assertFileExists(__DIR__ . '/testit/.journal-php/another', __DIR__ . '/testit/.journal-php/another after');
$test->assertFileExists(__DIR__ . '/testit/.journal-php/sub/deep/deep/thing',
                        __DIR__ . '/testit/.journal-php/sub/deep/deep/thing after');
$test->assertFileExists(__DIR__ . '/testit/.journal-php/anothernew/dir',
                        __DIR__ . '/testit/.journal-php/another/dir after');
$test->assertFileExists(__DIR__ . '/testit/.journal-php/anothernew/dir/file',
                        __DIR__ . '/testit/.journal-php/another/dir/file after');

$test->assertEquals(decoct(0777), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-php/sub/deep/deep/thing')),
                    'perms ' . __DIR__ . '/testit/.journal-php/sub/deep/deep/thing');
$test->assertEquals(decoct(0755), decoct(0755 & fileperms(__DIR__ . '/testit/.journal-php/anothernew')),
                    'perms ' . __DIR__ . '/testit/.journal-php/anothernew');
$test->assertEquals(decoct(0777), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-php/anothernew/dir')),
                    'perms ' . __DIR__ . '/testit/.journal-php/anothernew/dir');

$test->assertEquals(filemtime(__DIR__ . '/testit/php/another'),
                    filemtime(__DIR__ . '/testit/.journal-php/another'), 'mtime 1');
$test->assertEquals(1234567, filemtime(__DIR__ . '/testit/.journal-php/foo'), 'foo mtime');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===