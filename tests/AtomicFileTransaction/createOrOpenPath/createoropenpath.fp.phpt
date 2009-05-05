--TEST--
PEAR2_Pyrus_AtomicFileTransaction::createOrOpenPath(), return open file pointer
--FILE--
<?php
define('MYDIR', __DIR__);
require dirname(__DIR__) . '/setup.empty.php.inc';

$role = new PEAR2_Pyrus_Installer_Role_Php(PEAR2_Pyrus_Config::current());
$atomic = new PEAR2_Pyrus_AtomicFileTransaction($role, __DIR__ . '/testit/src');

$atomic->begin();

$fp = $atomic->createOrOpenPath('foo', false, 0646);
fwrite($fp, 'hi');
fclose($fp);
$test->assertEquals('hi', file_get_contents(__DIR__ . '/testit/.journal-src/foo'), 'foo contents');
$test->assertEquals(decoct(0646), decoct(0777 & fileperms(__DIR__ . '/testit/.journal-src/foo')), 'perms set');
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