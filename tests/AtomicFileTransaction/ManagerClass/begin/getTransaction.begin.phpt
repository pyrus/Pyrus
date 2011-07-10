--TEST--
\Pyrus\AtomicFileTransaction\Manager::getTransaction(), begin transaction when manager begin() was called.
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$instance->begin();

$dir = TESTDIR . '/foo';
$transaction = $instance->getTransaction($dir);
$test->assertTrue($transaction->inTransaction(), 'Transaction must be started');

$dir = TESTDIR . '/foo1';
$transaction = $instance->getTransaction($dir);
$test->assertTrue($transaction->inTransaction(), 'Transaction must be started');

$instance->rollback();
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===