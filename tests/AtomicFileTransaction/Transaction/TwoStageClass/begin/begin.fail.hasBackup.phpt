--TEST--
\Pyrus\AtomicFileTransaction\Transaction\TwoStage::begin(), backup directory exists
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

touch($backupPath);

try {
    $instance->begin();
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('Cannot begin - a backup directory still exists', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===