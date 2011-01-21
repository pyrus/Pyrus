--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::removeBackups() failure, not in transaction
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

try {
    \PEAR2\Pyrus\AtomicFileTransaction::removeBackups();
    throw new Exception('Expected exception.');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot finish - not in a transaction', $e->getMessage(), 'error');
}

try {
    $atomic->commit();
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Transaction not active.', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===