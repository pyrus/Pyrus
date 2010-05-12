--TEST--
\PEAR2\Pyrus\Installer: install remote packages, --force
--FILE--
<?php

define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/install.force',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';

PEAR2\Pyrus\Main::$options['force'] = true;
\PEAR2\Pyrus\Installer::begin();
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P1'));
\PEAR2\Pyrus\Installer::prepare(new \PEAR2\Pyrus\Package('pear2/P2'));
\PEAR2\Pyrus\Installer::commit();
$reg = \PEAR2\Pyrus\Config::current()->registry;
$test->assertTrue(isset($reg->package["P1"]), "installed P1");
$test->assertTrue(isset($reg->package["P2"]), "installed P2");
$test->assertEquals('1.1.0a1', $reg->info('P1', 'pear2.php.net', 'version'), 'P1 version');
$test->assertEquals('alpha', $reg->info('P1', 'pear2.php.net', 'stability'), 'P1 stability');

$test->assertEquals('10000.345.56', $reg->package['pear2.php.net/P2']->dependencies['required']->php->min,
                    'P2 min PHP');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===