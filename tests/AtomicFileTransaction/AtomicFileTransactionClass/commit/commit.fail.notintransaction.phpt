--TEST--
\Pyrus\AtomicFileTransaction::commit() failure, not in transaction
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

try {
    \Pyrus\AtomicFileTransaction::commit();
    throw new Exception('Expected exception.');
} catch (\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot commit - not in a transaction', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===