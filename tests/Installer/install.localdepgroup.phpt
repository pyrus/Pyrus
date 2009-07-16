--TEST--
\pear2\Pyrus\Installer: install local packages with dependency groups
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/install.depgroup',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
\pear2\Pyrus\Installer::begin();
\pear2\Pyrus\Installer::prepare(new \pear2\Pyrus\Package(__DIR__ . '/../Mocks/Internet/install.depgroup/get/P2-1.0.0.tgz#group'));
\pear2\Pyrus\Installer::commit();
$reg = \pear2\Pyrus\Config::current()->registry;
$test->assertEquals('1.0.0', $reg->info('P2', 'pear2.php.net', 'version'), 'P2 version');
$test->assertEquals('1.0.0', $reg->info('P4', 'pear2.php.net', 'version'), 'P4 version');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===