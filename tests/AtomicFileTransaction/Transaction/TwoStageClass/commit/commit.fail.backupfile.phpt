--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$instance->begin();

touch($backupPath);

try {
    $instance->commit();
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('CRITICAL - unable to complete transaction, rename of actual to backup path failed', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===