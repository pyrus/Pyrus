--TEST--
\pear2\Pyrus\Channel\RemotePackages with old channels
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/../Mocks/Internet/paranoid',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

unset($chan->protocols->rest['REST1.3']);

$remote = $chan->remotepackages;
$package = $remote->getPackage('P1');
$test->assertEquals('P1', $package->name, 'right package');

foreach ($remote['stable'] as $key => $package) {
    $test->assertEquals('P1', $package->name, 'right package');
}

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===