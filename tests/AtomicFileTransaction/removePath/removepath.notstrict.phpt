--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::removePath() failure, not strict
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();
mkdir(TESTDIR . '/.journal-src/foo/bar', 0777, true);
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'before');
$atomic->removePath('foo', false);
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'should still exist');
\PEAR2\Pyrus\AtomicFileTransaction::rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===