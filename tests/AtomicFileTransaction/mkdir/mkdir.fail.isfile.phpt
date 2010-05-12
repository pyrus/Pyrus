--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::mkdir() failure, directory exists and is a file
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');
\PEAR2\Pyrus\AtomicFileTransaction::begin();

$atomic->createOrOpenPath('oops', 'hi');

try {
    $atomic->mkdir('oops');
    die('should have failed');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot create directory oops, it is a file', $e->getMessage(), 'error');
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