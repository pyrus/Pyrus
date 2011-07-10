--TEST--
\Pyrus\Installer: install local packages with dependency groups
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/install.depgroup',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
\Pyrus\Installer::begin();
\Pyrus\Installer::prepare(new \Pyrus\Package(__DIR__ . '/../Mocks/Internet/install.depgroup/get/P2-1.0.0.tgz#group'));
\Pyrus\Installer::commit();
$reg = \Pyrus\Config::current()->registry;
$test->assertEquals('1.0.0', $reg->info('P2', 'pear2.php.net', 'version'), 'P2 version');
$test->assertEquals('1.0.0', $reg->info('P4', 'pear2.php.net', 'version'), 'P4 version');

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===