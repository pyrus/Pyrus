--TEST--
\pear2\Pyrus\Installer: install remote packages that is signed with an OpenSSL signature
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/install.remote.signedpackage',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
\pear2\Pyrus\Installer::begin();
\pear2\Pyrus\Installer::prepare(new \pear2\Pyrus\Package('pear2/P1'));
\pear2\Pyrus\Installer::commit();
$reg = \pear2\Pyrus\Config::current()->registry;

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