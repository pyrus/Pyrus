--TEST--
\Pyrus\AtomicFileTransaction::removePath() failure, not strict
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

mkdir($journalPath . '/foo/bar', 0777, true);
$test->assertFileExists($journalPath . '/foo', 'before');
$instance->removePath('foo', false);
$test->assertFileExists($journalPath . '/foo', 'should still exist');
$instance->rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===