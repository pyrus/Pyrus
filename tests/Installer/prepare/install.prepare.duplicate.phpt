--TEST--
PEAR2_Pyrus_Installer::prepare(), duplicate package
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/../setup.php.inc';
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::prepare($package);
PEAR2_Pyrus_Installer::rollback();
// this passes if no exceptions are thrown
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===