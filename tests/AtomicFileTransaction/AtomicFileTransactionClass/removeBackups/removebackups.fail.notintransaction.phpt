--TEST--
\Pyrus\AtomicFileTransaction::removeBackups() failure, not in transaction
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

try {
    \Pyrus\AtomicFileTransaction::removeBackups();
    throw new Exception('Expected exception.');
} catch (\Pyrus\AtomicFileTransaction\Exception $e) {
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