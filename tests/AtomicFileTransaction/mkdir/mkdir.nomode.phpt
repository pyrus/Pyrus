--TEST--
\PEAR2\Pyrus\AtomicFileTransaction::mkdir(), use default mode
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$old = umask(0444); // confirm this does not affect things
\PEAR2\Pyrus\Config::current()->umask = 0002;
$atomic = \PEAR2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');
\PEAR2\Pyrus\AtomicFileTransaction::begin();

$atomic->mkdir('good');

\PEAR2\Pyrus\AtomicFileTransaction::commit();

$test->assertFileExists(__DIR__ . '/testit/src/good', __DIR__ . '/testit/src/good should exist');
$test->assertEquals(decoct(0775), decoct(0777 & fileperms(__DIR__ . '/testit/src/good')), 'permissions should work');
$test->assertEquals(decoct(0775), decoct(0777 & fileperms(__DIR__ . '/testit/src')), 'permissions should work src');
umask($old);
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===