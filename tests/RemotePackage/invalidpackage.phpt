--TEST--
\PEAR2\Pyrus\Channel\RemotePackage::download(), invalid tar/tgz
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/invalidpackage',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
$remote = new \PEAR2\Pyrus\Channel\RemotePackage(\PEAR2\Pyrus\Config::current()->channelregistry['pear2.php.net'],
                                                'stable');
$remote = $remote['GetMaintainers_Test'];
$remote->version['release'] = '1.0.0';
try {
    $remote->download();
    throw new Exception('should fail and did not');
} catch (PEAR2\Pyrus\Package\Exception $e) {
    $test->assertEquals('Invalid abstract package ' .
                        'pear2.php.net/GetMaintainers_Test',
                        $e->getMessage(), 'message');
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
