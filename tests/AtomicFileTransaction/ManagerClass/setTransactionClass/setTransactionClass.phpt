--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Manager::setTransactionClass()
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
require dirname(__DIR__) . '/mocks/TransactionMock.php.inc';

$instance->setTransactionClass('TransactionMock');
$test->assertEquals('TransactionMock', $instance->getTransactionClass(), 'must be equal')
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===