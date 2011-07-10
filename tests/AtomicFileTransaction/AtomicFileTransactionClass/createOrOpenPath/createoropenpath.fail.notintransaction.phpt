--TEST--
\Pyrus\AtomicFileTransaction::createOrOpenPath() failure, not in transaction
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

try {
    $atomic->createOrOpenPath('foo');
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Transaction not active.', $e->getMessage(), 'error');
}

try {
    $atomic->openPath('foo');
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