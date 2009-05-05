--TEST--
PEAR2_Pyrus_AtomicFileTransaction::createOrOpenPath(), contents is stream
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$role = new PEAR2_Pyrus_Installer_Role_Php(PEAR2_Pyrus_Config::current());
$atomic = new PEAR2_Pyrus_AtomicFileTransaction($role, __DIR__ . '/testit/src');

$atomic->begin();

file_put_contents(__DIR__ . '/testit/blah', 'blah');
$fp = fopen(__DIR__ . '/testit/blah', 'rb');
$atomic->createOrOpenPath('foo', $fp, 0664);
fclose($fp);
$test->assertEquals('blah', file_get_contents(__DIR__ . '/testit/.journal-src/foo'), 'blah contents');
$test->assertEquals(decoct(0664), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-src/foo')), 'perms set');
$atomic->rollback();
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===