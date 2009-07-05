--TEST--
\pear2\Pyrus\AtomicFileTransaction::repair()
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.php.inc';
\pear2\Pyrus\Config::current()->ext_dir = __DIR__ . '/testit/ext';
mkdir(__DIR__ . '/testit/.old-ext');
mkdir(__DIR__ . '/testit/src');
mkdir(__DIR__ . '/testit/.old-src');
touch(__DIR__ . '/testit/.old-src/foo');

\pear2\Pyrus\AtomicFileTransaction::repair();

$test->assertFileExists(__DIR__ . '/testit/src/foo', __DIR__ . '/testit/src/foo');
$test->assertFileExists(__DIR__ . '/testit/ext', __DIR__ . '/testit/ext');
$test->assertFileNotExists(__DIR__ . '/testit/.old-ext', __DIR__ . '/testit/.old-ext');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-ext', __DIR__ . '/testit/src/.journal-ext');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src/foo', __DIR__ . '/testit/.journal-src/foo');
$test->assertFileNotExists(__DIR__ . '/testit/.journal-src', __DIR__ . '/testit/.journal-src');
$test->assertFileNotExists(__DIR__ . '/testit/.old-src', __DIR__ . '/testit/.old-src');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===