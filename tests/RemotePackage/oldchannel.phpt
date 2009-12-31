--TEST--
\pear2\Pyrus\Channel\RemotePackage with a channel that only supports REST1.0
--SKIPIF--
<?php
die('skip for now');
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/validzip',
                       'http://pear2.php.net/');
\pear2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \pear2\Pyrus\Config::current()->channelregistry['pear2.php.net'];
unset($chan->protocols->rest['REST1.3']);

$remote = new \pear2\Pyrus\Channel\Remotepackage($chan,
                                                'stable');
$remote->name = 'GetMaintainers_Test';
$remote = $remote['GetMaintainers_Test'];
$test->assertEquals('pear2\Pyrus\Channel\Remotepackage', get_class($remote), 'right class');

$remote = new \pear2\Pyrus\Channel\Remotepackage($chan,
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
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===
