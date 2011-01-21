--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Manager::getTransaction(), cannot begin transaction twice.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

try {
    $instance->rollback();
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot rollback - not in a transaction', $e->getMessage(), 'in transaction');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===