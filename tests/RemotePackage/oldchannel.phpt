--TEST--
\Pyrus\Channel\RemotePackage with a channel that only supports REST1.0
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/validzip',
                       'http://pear2.php.net/');
\Pyrus\Main::$downloadClass = 'Internet';
$chan = \Pyrus\Config::current()->channelregistry['pear2.php.net'];
unset($chan->protocols->rest['REST1.3']);

$remote = new \Pyrus\Channel\RemotePackage($chan,
                                                'stable');
$remote->name = 'GetMaintainers_Test';
$remote = $remote['GetMaintainers_Test'];
$test->assertEquals('Pyrus\Channel\RemotePackage', get_class($remote), 'right class');

$remote = new \Pyrus\Channel\RemotePackage($chan,
                                                'stable');
$remote->name = 'GetMaintainers_Test';

$versions = array();
foreach ($remote as $version => $info) {
    $versions[$version] = $info;
}

$test->assertEquals(array('1.0.0' => array('stability' => 'stable', 'minimumphp' => '5.2.0')),
                    $versions, 'iterated info');
$test->assertEquals(true, isset($remote['GetMaintainers_Test']), 'isset true');
$test->assertEquals(false, isset($remote['foo']), 'isset false');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===
