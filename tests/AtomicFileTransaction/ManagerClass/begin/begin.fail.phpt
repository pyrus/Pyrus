--TEST--
\Pyrus\AtomicFileTransaction\Manager::getTransaction(), cannot begin transaction twice.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
require dirname(__DIR__) . '/mocks/TransactionMock.php.inc';

$instance->setTransactionClass('TransactionMock');
TransactionMock::$failBegin = true;

$instance->getTransaction(TESTDIR . '/foo');

try {
    $instance->begin();
    throw new \Exception('Expected exception');
} catch (\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Unable to begin transaction', $e->getMessage(), 'begin failed');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===