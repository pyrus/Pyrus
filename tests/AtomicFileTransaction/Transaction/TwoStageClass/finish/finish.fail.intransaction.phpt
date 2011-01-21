--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$instance->begin();

$test->assertTrue($instance->inTransaction(), 'In transaction');
try {
    $instance->finish();
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('Cannot finish - still in a transaction', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===