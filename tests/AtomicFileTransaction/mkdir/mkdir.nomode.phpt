--TEST--
PEAR2_Pyrus_AtomicFileTransaction::mkdir(), use default mode
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

PEAR2_Pyrus_Config::current()->umask = 0775;
$atomic = PEAR2_Pyrus_AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');
PEAR2_Pyrus_AtomicFileTransaction::begin();

$atomic->mkdir('good');

PEAR2_Pyrus_AtomicFileTransaction::commit();

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