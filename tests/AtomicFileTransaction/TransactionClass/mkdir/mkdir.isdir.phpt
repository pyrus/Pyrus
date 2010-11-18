--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::mkdir(), use default mode
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

mkdir($journalPath . '/good');

$instance->mkdir('good');

$instance->commit();

$test->assertFileExists($path . '/good', $path . '/good should exist');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===