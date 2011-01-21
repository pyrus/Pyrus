--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::repair()
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
\PEAR2\Pyrus\Config::current()->ext_dir = TESTDIR . '/ext';
mkdir(TESTDIR . '/.old-ext');
mkdir(TESTDIR . '/src');
mkdir(TESTDIR . '/.old-src');
touch(TESTDIR . '/.old-src/foo');

\PEAR2\Pyrus\AtomicFileTransaction::repair();

$test->assertFileExists(TESTDIR . '/src/foo', TESTDIR . '/src/foo');
$test->assertFileExists(TESTDIR . '/ext', TESTDIR . '/ext');
$test->assertFileNotExists(TESTDIR . '/.old-ext', TESTDIR . '/.old-ext');
$test->assertFileNotExists(TESTDIR . '/.journal-ext', TESTDIR . '/src/.journal-ext');
$test->assertFileNotExists(TESTDIR . '/.journal-src/foo', TESTDIR . '/.journal-src/foo');
$test->assertFileNotExists(TESTDIR . '/.journal-src', TESTDIR . '/.journal-src');
$test->assertFileNotExists(TESTDIR . '/.old-src', TESTDIR . '/.old-src');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===