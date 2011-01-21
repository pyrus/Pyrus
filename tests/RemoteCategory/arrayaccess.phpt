--TEST--
\PEAR2\Pyrus\Channel\RemoteCategory: ArrayAccess and iteration test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
require __DIR__ . '/../Mocks/Internet.php';

Internet::addDirectory(__DIR__ . '/noreleases',
                       'http://pear2.php.net/');
\PEAR2\Pyrus\Main::$downloadClass = 'Internet';
$chan = \PEAR2\Pyrus\Config::current()->channelregistry['pear2.php.net'];

$category = $chan->remotecategories['Default'];

$test->assertEquals(true, isset($chan->remotecategories['Default']['GetMaintainers_Test']), 'isset 1');
$test->assertEquals(false, isset($chan->remotecategories['Default']['nonexisting']), 'isset 2');
$test->assertEquals('PEAR2\Pyrus\Channel\RemotePackage',
                    get_class($chan->remotecategories['Default']['GetMaintainers_Test']),
                    'offsetGet test');

foreach ($chan->remotecategories['Default'] as $name => $package) {
    $test->assertEquals('GetMaintainers_Test', $name, 'right name');
    $test->assertEquals('PEAR2\Pyrus\Channel\RemotePackage', get_class($package), 'right class');
    $test->assertEquals('GetMaintainers_Test', $package->name, 'right remote package');
}
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===
