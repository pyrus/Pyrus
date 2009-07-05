--TEST--
\pear2\Pyrus\AtomicFileTransaction::mkdir(), use default mode
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

\pear2\Pyrus\Config::current()->umask = 0775;
$atomic = \pear2\Pyrus\AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');
\pear2\Pyrus\AtomicFileTransaction::begin();

$atomic->mkdir('good');

\pear2\Pyrus\AtomicFileTransaction::commit();

$test->assertFileExists(__DIR__ . '/testit/src/good', __DIR__ . '/testit/src/good should exist');
$test->assertEquals(decoct(0775), decoct(0777 & fileperms(__DIR__ . '/testit/src/good')), 'permissions should work');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===