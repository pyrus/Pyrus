--TEST--
PEAR2_Pyrus_AtomicFileTransaction::createOrOpenPath() failure, not in transaction
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = PEAR2_Pyrus_AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

try {
    $atomic->createOrOpenPath('foo');
    die('should have failed');
} catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
    $test->assertEquals('Cannot create foo - not in a transaction', $e->getMessage(), 'error');
}

try {
    $atomic->openPath('foo');
    die('should have failed');
} catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
    $test->assertEquals('Cannot open foo - not in a transaction', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===