--TEST--
\Pyrus\AtomicFileTransaction::begin(), journal dir exists
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

mkdir(TESTDIR . '/.journal-src');

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

$test->assertFileNotExists(TESTDIR . '/src/', 'before');
$test->assertFileExists(TESTDIR . '/.journal-src/', 'before');

$atomic->begin();

$test->assertFileNotExists(TESTDIR . '/src/', 'after');
$test->assertFileExists(TESTDIR . '/.journal-src/', 'after');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===