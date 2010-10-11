--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::begin()
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';
$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

$test->assertFileNotExists(TESTDIR . '/src/', 'before');
$test->assertFileNotExists(TESTDIR . '/.journal-src/', 'before');

$atomic->begin();

$test->assertFileNotExists(TESTDIR . '/src/', 'after');
$test->assertFileExists(TESTDIR . '/.journal-src/', 'after');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===