--TEST--
\Pyrus\AtomicFileTransaction\Manager::getTransaction(), cannot begin transaction twice.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
require dirname(__DIR__) . '/mocks/TransactionMock.php.inc';

$instance->setTransactionClass('TransactionMock');
TransactionMock::$failRollback = true;

$instance->getTransaction(TESTDIR . '/foo');
$instance->begin();

try {
    $instance->rollback();
    throw new \Exception('Expected exception');
} catch (\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Warning: rollback did not succeed for all transactions', $e->getMessage(), 'rollback failed');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===