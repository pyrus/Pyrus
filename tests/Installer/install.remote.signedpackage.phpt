--TEST--
PEAR2_Pyrus_Installer: install remote packages that is signed with an OpenSSL signature
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/install.remote.signedpackage',
                       'http://pear2.php.net/');
PEAR2_Pyrus::$downloadClass = 'Internet';
PEAR2_Pyrus_Installer::begin();
PEAR2_Pyrus_Installer::prepare(new PEAR2_Pyrus_Package('pear2/P1'));
PEAR2_Pyrus_Installer::commit();
$reg = PEAR2_Pyrus_Config::current()->registry;

$test->assertTrue(isset($reg->package["P1"]), "installed P1");
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===