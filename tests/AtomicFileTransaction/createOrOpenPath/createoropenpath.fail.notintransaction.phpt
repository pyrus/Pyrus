--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::createOrOpenPath() failure, not in transaction
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

try {
    $atomic->createOrOpenPath('foo');
    throw new Exception('Expected exception.');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot create foo - not in a transaction', $e->getMessage(), 'error');
}

try {
    $atomic->openPath('foo');
    throw new Exception('Expected exception.');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot open foo - not in a transaction', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===