--TEST--
PEAR2_Pyrus_AtomicFileTransaction::begin() failure, in transaction
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$role = new PEAR2_Pyrus_Installer_Role_Php(PEAR2_Pyrus_Config::current());
$atomic = new PEAR2_Pyrus_AtomicFileTransaction($role, __DIR__ . '/testit/src');

$atomic->begin();
try {
    $atomic->begin();
    die('should have failed');
} catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
    $test->assertEquals('Cannot begin - already in a transaction', $e->getMessage(), 'error');
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===