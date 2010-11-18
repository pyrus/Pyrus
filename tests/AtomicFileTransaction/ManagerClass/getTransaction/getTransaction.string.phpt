--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Manager::getTransaction(), string path
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$dir = TESTDIR . '/foo';
$transaction = $instance->getTransaction($dir);

$test->assertSame($transaction, $instance->getTransaction($dir), 'must return the same instance');
$test->assertIsa('PEAR2\Pyrus\AtomicFileTransaction\Transaction', $transaction, 'must be a Transaction instance');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===