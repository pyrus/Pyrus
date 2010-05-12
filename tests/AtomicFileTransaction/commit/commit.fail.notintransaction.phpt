--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::commit() failure, not in transaction
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

try {
    \PEAR2\Pyrus\AtomicFileTransaction::commit();
    die('should have failed');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot commit - not in a transaction', $e->getMessage(), 'error');
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