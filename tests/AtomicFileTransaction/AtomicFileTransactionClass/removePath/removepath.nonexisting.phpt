--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::removePath(), path doesn't exist
--FILE--
<?php
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(TESTDIR . '/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();

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