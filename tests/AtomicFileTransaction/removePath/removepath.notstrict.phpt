--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::removePath() failure, not strict
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

\PEAR2\Pyrus\AtomicFileTransaction::begin();
mkdir(__DIR__ . '/testit/.journal-src/foo/bar', 0777, true);
$test->assertFileExists(__DIR__ . '/testit/.journal-src/foo', 'before');
$atomic->removePath('foo', false);
$test->assertFileExists(__DIR__ . '/testit/.journal-src/foo', 'should still exist');
\PEAR2\Pyrus\AtomicFileTransaction::rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===