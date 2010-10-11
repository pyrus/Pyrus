--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::removePath() failure, strict
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();
mkdir(TESTDIR . '/.journal-src/foo/bar', 0777, true);
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'before');
try {
    $atomic->removePath('foo');
    throw new Exception('Expected exception.');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot remove directory foo in ' . TESTDIR . DIRECTORY_SEPARATOR . '.journal-src', $e->getMessage(), 'error');
}
$test->assertFileExists(TESTDIR . '/.journal-src/foo', 'should still exist');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===