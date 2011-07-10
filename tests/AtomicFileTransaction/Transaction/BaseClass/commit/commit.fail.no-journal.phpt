--TEST--
\Pyrus\AtomicFileTransaction\Transaction\Base::__construct()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$instance->begin();

rmdir($journalPath);

try {
    $instance->commit();
    throw new Exception('Expected exception.');
} catch (\RuntimeException $e) {
    $test->assertEquals('CRITICAL - unable to complete transaction, rename of journal to actual path failed', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===