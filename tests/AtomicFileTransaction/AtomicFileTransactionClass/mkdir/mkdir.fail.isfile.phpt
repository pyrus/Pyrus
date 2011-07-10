--TEST--
\Pyrus\AtomicFileTransaction::mkdir() failure, directory exists and is a file
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');
\Pyrus\AtomicFileTransaction::begin();

$atomic->createOrOpenPath('oops', 'hi');

try {
    $atomic->mkdir('oops');
    throw new Exception('Expected exception.');
} catch (RuntimeException $e) {
    $test->assertEquals('Cannot create directory oops, it is a file', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===