--TEST--
\pear2\Pyrus\AtomicFileTransaction::repair() fail
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.php.inc';
\pear2\Pyrus\Config::current()->ext_dir = __DIR__ . '/testit/ext';
mkdir(__DIR__ . '/testit/.old-ext');
touch(__DIR__ . '/testit/php');
mkdir(__DIR__ . '/testit/.old-php');
touch(__DIR__ . '/testit/.old-php/foo');

\pear2\Pyrus\AtomicFileTransaction::begin();
try {
    \pear2\Pyrus\AtomicFileTransaction::repair();
    throw new Exception('should have failed');
} catch (\pear2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Cannot repair while in a transaction', $e->getMessage(), 'error transaction');
}
\pear2\Pyrus\AtomicFileTransaction::rollback();

try {
    \pear2\Pyrus\AtomicFileTransaction::repair();
    throw new Exception('should have failed');
} catch (\pear2\Pyrus\AtomicFileTransaction\Exception $e) {
    $test->assertEquals('Repair failed - php_dir path ' . __DIR__ . DIRECTORY_SEPARATOR .
                        'testit' . DIRECTORY_SEPARATOR . 'php is not a directory.  ' .
                        'Move this file out of the way and try the repair again', $e->getMessage(), 'error');
}

$test->assertFileNotExists(__DIR__ . '/testit/php/foo', __DIR__ . '/testit/php/foo');
$test->assertFileNotExists(__DIR__ . '/testit/ext', __DIR__ . '/testit/ext');
$test->assertFileExists(__DIR__ . '/testit/.old-ext', __DIR__ . '/testit/.old-ext');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-php', __DIR__ . '/testit/.journal-php');
$test->assertFileExists(__DIR__ . '/testit/.old-php', __DIR__ . '/testit/.old-php');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===