--TEST--
\PEAR2\Pyrus\AtomicFileTransaction\Manager::getTransaction(), string SplFileInfo
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';

$role = new \PEAR2\Pyrus\Installer\Role\Php(\PEAR2\Pyrus\Config::current(), \PEAR2\Pyrus\Installer\Role::getInfo('php'));
$transaction = $instance->getTransaction($role);

$test->assertSame($transaction, $instance->getTransaction($role), 'must return the same instance');
$test->assertIsa('PEAR2\Pyrus\AtomicFileTransaction\Transaction', $transaction, 'must be a Transaction instance');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===