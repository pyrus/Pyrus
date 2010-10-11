--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::repair() fail
--FILE--
<?php
require dirname(__DIR__) . '/setup.php.inc';
\PEAR2\Pyrus\Config::current()->ext_dir = TESTDIR . '/ext';
mkdir(TESTDIR . '/.old-ext');
touch(TESTDIR . '/php');
mkdir(TESTDIR . '/.old-php');
touch(TESTDIR . '/.old-php/foo');

\PEAR2\Pyrus\AtomicFileTransaction::begin();
try {
    \PEAR2\Pyrus\AtomicFileTransaction::repair();
    throw new Exception('should have failed');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot repair while in a transaction', $e->getMessage(), 'error transaction');
}
\PEAR2\Pyrus\AtomicFileTransaction::rollback();

try {
    \PEAR2\Pyrus\AtomicFileTransaction::repair();
    throw new Exception('should have failed');
} catch (\PEAR2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Repair failed - php_dir path ' . TESTDIR . DIRECTORY_SEPARATOR . 'php is not a directory.  ' .
                        'Move this file out of the way and try the repair again', $e->getMessage(), 'error');
}

$test->assertFileNotExists(TESTDIR . '/php/foo', TESTDIR . '/php/foo');
$test->assertFileNotExists(TESTDIR . '/ext', TESTDIR . '/ext');
$test->assertFileExists(TESTDIR . '/.old-ext', TESTDIR . '/.old-ext');
$test->assertFileNotExists(TESTDIR . '/.journal-php', TESTDIR . '/.journal-php');
$test->assertFileExists(TESTDIR . '/.old-php', TESTDIR . '/.old-php');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===