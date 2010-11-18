--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::removePath(), path doesn't exist
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$test->assertFileNotExists($journalPath . '/foo', 'before');
$instance->removePath('foo');
$test->assertFileNotExists($journalPath . '/foo', 'should still exist');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===