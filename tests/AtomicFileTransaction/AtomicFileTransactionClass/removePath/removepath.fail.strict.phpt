--TEST--
\Pyrus\AtomicFileTransaction::removePath() failure, strict
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\Pyrus\AtomicFileTransaction::begin();
mkdir(TESTDIR . '/.journal-src/foo/bar', 0777, true);
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'before');
try {
    $atomic->removePath('foo');
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Cannot remove directory foo in ' . TESTDIR . DIRECTORY_SEPARATOR . '.journal-src', $e->getMessage(), 'error');
}
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'should still exist');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===