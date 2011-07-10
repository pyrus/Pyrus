--TEST--
\Pyrus\AtomicFileTransaction::removePath() failure, not strict
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\Pyrus\AtomicFileTransaction::begin();
mkdir(TESTDIR . '/.journal-src/foo/bar', 0777, true);
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'before');
$atomic->removePath('foo', false);
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'should still exist');
\Pyrus\AtomicFileTransaction::rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===