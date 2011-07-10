--TEST--
\Pyrus\AtomicFileTransaction\Manager::getTransaction(), cannot begin transaction twice.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

try {
    $instance->commit();
} catch (\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot commit - not in a transaction', $e->getMessage(), 'in transaction');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===