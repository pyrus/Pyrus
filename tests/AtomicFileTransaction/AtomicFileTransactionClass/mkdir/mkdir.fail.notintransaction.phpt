--TEST--
\Pyrus\AtomicFileTransaction::mkdir() failure, not in transaction
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

try {
    $atomic->mkdir('oops');
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