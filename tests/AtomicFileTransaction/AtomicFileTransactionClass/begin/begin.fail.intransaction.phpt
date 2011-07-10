--TEST--
\Pyrus\AtomicFileTransaction::begin() failure, in transaction
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

$atomic->begin();
try {
    $atomic->begin();
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Cannot begin - already in a transaction', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===