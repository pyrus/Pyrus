--TEST--
\Pyrus\Channel\RemotePackages package has single release
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/installer.prepare.dep.versionconflict',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
$chan = \Pyrus\Config::current()->channelregistry['pear2.php.net'];

$remote = $chan->remotepackages;
$package = $remote->getPackage('P1');
$test->assertEquals('P1', $package->name, 'right package');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===