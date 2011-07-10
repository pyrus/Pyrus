--TEST--
\Pyrus\AtomicFileTransaction::removePath(), path doesn't exist
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\Pyrus\AtomicFileTransaction::begin();

$test->assertFileNotExists(TESTDIR . '/.journal-src/foo', 'before');
$atomic->removePath('foo');
$test->assertFileNotExists(TESTDIR . '/.journal-src/foo', 'should still exist');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===