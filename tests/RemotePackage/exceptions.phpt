--TEST--
\PEAR2\Pyrus\Channel\RemotePackage exceptions
--SKIPIF--
<?php
if (!extension_loaded('openssl')) die('SKIP openssl required');
?>
--FILE--
<?php
define('MYDIR', __DIR__);
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/validzip',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \PEAR2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

$remote = new \PEAR2\Pyrus\Channel\RemotePackage($chan,
                                                'stable');

try {
    $remote['foo'] = 1;
    throw new Exception('should have failed and did not');
} catch (PEAR2\Pyrus\Channel\Exception $e) {
    $test->assertEquals('remote channel info is read-only', $e->getMessage(), 'offsetSet');
}

try {
    unset($remote['foo']);
    throw new Exception('should have failed and did not');
} catch (PEAR2\Pyrus\Channel\Exception $e) {
    $test->assertEquals('remote channel info is read-only', $e->getMessage(), 'offsetUnset');
}

try {
    foreach ($remote as $oops) {
        
    }
    throw new Exception('should have failed and did not');
} catch (PEAR2\Pyrus\Channel\Exception $e) {
    $test->assertEquals('Cannot iterate without first choosing a remote package',
                        $e->getMessage(), 'iterating without offsetGet');
}

$remote = $remote['GetMaintainers_Test'];
$remote->version['release'] = '1.0.0';

$versions = array();
foreach ($remote as $version => $info) {
    $versions[$version] = $info;
}

$test->assertEquals(array('1.0.0' => array('stability' => 'stable', 'minimumphp' => '5.2.0')),
                    $versions, 'iterated info');
$test->assertEquals(true, isset($remote['GetMaintainers_Test']), 'isset true');
$test->assertEquals(false, isset($remote['foo']), 'isset false');

$chan = \PEAR2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

unset($chan->protocols->rest['REST1.0']);

try {
    $remote = new \PEAR2\Pyrus\Channel\RemotePackage($chan,
                                                    'stable');
    throw new Exception('should have failed and did not');
} catch (PEAR2\Pyrus\Channel\Exception $e) {
    $test->assertEquals('Cannot access remote packages without REST1.0 protocol',
                        $e->getMessage(), 'iterating without offsetGet');
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
