--TEST--
PEAR2_Pyrus_AtomicFileTransaction::repair() fail
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.php.inc';
PEAR2_Pyrus_Config::current()->ext_dir = __DIR__ . '/testit/ext';
mkdir(__DIR__ . '/testit/.old-ext');
touch(__DIR__ . '/testit/src');
mkdir(__DIR__ . '/testit/.old-src');
touch(__DIR__ . '/testit/.old-src/foo');

PEAR2_Pyrus_AtomicFileTransaction::begin();
try {
    PEAR2_Pyrus_AtomicFileTransaction::repair();
    throw new Exception('should have failed');
} catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
    $test->assertEquals('Cannot repair while in a transaction', $e->getMessage(), 'error transaction');
}
PEAR2_Pyrus_AtomicFileTransaction::rollback();

try {
    PEAR2_Pyrus_AtomicFileTransaction::repair();
    throw new Exception('should have failed');
} catch (PEAR2_Pyrus_AtomicFileTransaction_Exception $e) {
    $test->assertEquals('Repair failed - php_dir path ' . __DIR__ . DIRECTORY_SEPARATOR .
                        'testit' . DIRECTORY_SEPARATOR . 'src is not a directory.  ' .
                        'Move this file out of the way and try the repair again', $e->getMessage(), 'error');
}

$test->assertFileNotExists(__DIR__ . '/testit/src/foo', __DIR__ . '/testit/src/foo');
$test->assertFileNotExists(__DIR__ . '/testit/ext', __DIR__ . '/testit/ext');
$test->assertFileExists(__DIR__ . '/testit/.old-ext', __DIR__ . '/testit/.old-ext');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src', __DIR__ . '/testit/.journal-src');
$test->assertFileExists(__DIR__ . '/testit/.old-src', __DIR__ . '/testit/.old-src');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===