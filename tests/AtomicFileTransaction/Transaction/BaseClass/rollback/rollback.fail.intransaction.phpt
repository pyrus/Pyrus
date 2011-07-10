--TEST--
\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$test->assertFalse($instance->inTransaction(), 'not in transaction');
try {
    $instance->rollback();
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('Transaction not active.', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===