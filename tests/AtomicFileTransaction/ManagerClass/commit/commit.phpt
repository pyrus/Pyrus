--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Manager::getTransaction(), cannot begin transaction twice.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

mkdir(TESTDIR . '/foo');
touch(TESTDIR . '/foo/test');
mkdir(TESTDIR . '/foo/1');
touch(TESTDIR . '/foo/1/test');

$instance->begin();
$instance->getTransaction(TESTDIR . '/foo');

// Check journal
$test->assertFileExists(TESTDIR . '/.journal-foo', 'before');
$test->assertFileExists(TESTDIR . '/.journal-foo/test', 'before');
$test->assertFileExists(TESTDIR . '/.journal-foo/1', 'before');
$test->assertFileExists(TESTDIR . '/.journal-foo/1/test', 'before');

// Check original
$test->assertFileExists(TESTDIR . '/foo', 'before');
$test->assertFileExists(TESTDIR . '/foo/test', 'before');
$test->assertFileExists(TESTDIR . '/foo/1', 'before');
$test->assertFileExists(TESTDIR . '/foo/1/test', 'before');

// Check old/backup
$test->assertFileNotExists(TESTDIR . '/.old-foo', 'before');
$test->assertFileNotExists(TESTDIR . '/.old-foo/test', 'before');
$test->assertFileNotExists(TESTDIR . '/.old-foo/1', 'before');
$test->assertFileNotExists(TESTDIR . '/.old-foo/1/test', 'before');

// Commit
$instance->commit();

// Check journal
$test->assertFileNotExists(TESTDIR . '/.journal-foo', 'after');
$test->assertFileNotExists(TESTDIR . '/.journal-foo/test', 'after');
$test->assertFileNotExists(TESTDIR . '/.journal-foo/1', 'after');
$test->assertFileNotExists(TESTDIR . '/.journal-foo/1/test', 'after');

// Check original
$test->assertFileExists(TESTDIR . '/foo', 'after');
$test->assertFileExists(TESTDIR . '/foo/test', 'after');
$test->assertFileExists(TESTDIR . '/foo/1', 'after');
$test->assertFileExists(TESTDIR . '/foo/1/test', 'after');

// Check old/backup
$test->assertFileExists(TESTDIR . '/.old-foo', 'after');
$test->assertFileExists(TESTDIR . '/.old-foo/test', 'after');
$test->assertFileExists(TESTDIR . '/.old-foo/1', 'after');
$test->assertFileExists(TESTDIR . '/.old-foo/1/test', 'after');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===