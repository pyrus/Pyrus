--TEST--
\Pyrus\AtomicFileTransaction\Manager::getTransaction(), cannot begin transaction twice.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$instance->begin();

try {
    $instance->begin();
} catch (\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot begin - already in a transaction', $e->getMessage(), 'in transaction');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===