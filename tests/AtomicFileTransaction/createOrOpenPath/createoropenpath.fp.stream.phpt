--TEST--
PEAR2_Pyrus_AtomicFileTransaction::createOrOpenPath(), contents is stream
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$atomic = PEAR2_Pyrus_AtomicFileTransaction::getTransactionObject(__DIR__ . '/testit/src');

PEAR2_Pyrus_AtomicFileTransaction::begin();

file_put_contents(__DIR__ . '/testit/blah', 'blah');
$fp = fopen(__DIR__ . '/testit/blah', 'rb');
$atomic->createOrOpenPath('foo', $fp, 0664);
fclose($fp);
$test->assertEquals('blah', file_get_contents(__DIR__ . '/testit/.journal-src/foo'), 'blah contents');
$test->assertEquals(decoct(0664), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-src/foo')), 'perms set');
PEAR2_Pyrus_AtomicFileTransaction::rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===