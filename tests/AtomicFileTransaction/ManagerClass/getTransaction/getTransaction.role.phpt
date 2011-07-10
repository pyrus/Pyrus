--TEST--
\Pyrus\AtomicFileTransaction\Manager::getTransaction(), string SplFileInfo
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$role = new \Pyrus\Installer\Role\Php(\Pyrus\Config::current(), \Pyrus\Installer\Role::getInfo('php'));
$transaction = $instance->getTransaction($role);

$test->assertSame($transaction, $instance->getTransaction($role), 'must return the same instance');
$test->assertIsa('Pyrus\AtomicFileTransaction\Transaction', $transaction, 'must be a Transaction instance');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===