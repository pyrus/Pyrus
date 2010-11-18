--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Manager::getTransaction(), cannot begin transaction twice.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
require dirname(__DIR__) . '/mocks/TwoStage.php';

$instance->setTransactionClass('TwoStage');
TwoStage::$failBegin = true;

$instance->getTransaction(TESTDIR . '/foo');

try {
    $instance->begin();
    throw new \Exception('Expected exception');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
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