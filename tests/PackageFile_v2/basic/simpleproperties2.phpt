--TEST--
PackageFile v2: test SimpleProperty
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$package = new PEAR2_Pyrus_PackageFile_v2;

$test->assertEquals(true, isset($package->version['release']), 'isset release version before');
$test->assertEquals(true, isset($package->version['api']), 'isset api version before');
$test->assertEquals(true, isset($package->stability['release']), 'isset release stability before');
$test->assertEquals(true, isset($package->stability['api']), 'isset api stability before');

$test->assertEquals('0.1.0', $package->version['release'], 'release version before');
$test->assertEquals('0.1.0', $package->version['api'], 'api version before');
$test->assertEquals(array('release' => '0.1.0', 'api' => '0.1.0'), $package->version->getInfo(), 'getinfo');
$test->assertEquals(array('release' => 'devel', 'api' => 'alpha'), $package->stability->getInfo(), 'getinfo stability');
$test->assertEquals('devel', $package->stability['release'], 'release stability before');
$test->assertEquals('alpha', $package->stability['api'], 'api stability before');
unset($package->version['release']);
unset($package->version['api']);
unset($package->stability['release']);
unset($package->stability['api']);
$test->assertEquals(null, $package->version['release'], 'release version after');
$test->assertEquals(null, $package->version['api'], 'api version after');
$test->assertEquals(null, $package->stability['release'], 'release stability after');
$test->assertEquals(null, $package->stability['api'], 'api stability after');

$test->assertEquals(false, isset($package->version['release']), 'isset release version after');
$test->assertEquals(false, isset($package->version['api']), 'isset api version after');
$test->assertEquals(false, isset($package->stability['release']), 'isset release stability after');
$test->assertEquals(false, isset($package->stability['api']), 'isset api stability after');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===