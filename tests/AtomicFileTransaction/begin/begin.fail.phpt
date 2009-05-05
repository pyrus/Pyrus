--TEST--
PEAR2_Pyrus_AtomicFileTransaction::begin(), journal dir exists as file
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

touch(__DIR__ . '/testit/.journal-src');

$role = new PEAR2_Pyrus_Installer_Role_Php(PEAR2_Pyrus_Config::current());
$atomic = new PEAR2_Pyrus_AtomicFileTransaction($role, __DIR__ . '/testit/src');

try {
    $atomic->begin();
} catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
    $test->assertEquals('unrecoverable transaction error: journal path ' .
                        __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR .
                        '.journal-src exists and is not a directory', $e->getMessage(), 'error message');
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