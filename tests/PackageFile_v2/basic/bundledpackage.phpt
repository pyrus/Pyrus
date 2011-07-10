--TEST--
PackageFile v2: test SimpleProperty
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$package = new \Pyrus\PackageFile\v2;

$package->fromArray(array('package' => array('contents' => array(
    'bundledpackage' => 'test'))));

$test->assertEquals(false, $package->bundledpackage, 'first test, no <bundle> tag present');

$package->fromArray(array('package' => array('contents' => array(
    'bundledpackage' => 'test'), 'bundle' => '')));

$test->assertEquals('test', $package->bundledpackage['test'], 'test');
$test->assertEquals(false, $package->bundledpackage['notfound'], 'notfound');

unset($package->bundledpackage['test']);
$test->assertEquals(false, $package->bundledpackage['test'], 'after unset');
unset($package->bundledpackage['test']);

$test->assertEquals(false, isset($package->bundledpackage['another']), 'isset before');
$test->assertEquals(false, $package->bundledpackage['another'], 'before set');
$test->assertEquals(0, count($package->bundledpackage), 'count 0');

$package->bundledpackage[] = 'another';

$test->assertEquals('another', $package->bundledpackage['another'], 'after set');
$test->assertEquals(true, isset($package->bundledpackage['another']), 'isset after');
$test->assertEquals(1, count($package->bundledpackage), 'count 1');

$package->bundledpackage[] = 'another';
$test->assertEquals(1, count($package->bundledpackage), 'still count 1');
$test->assertEquals('another', $package->bundledpackage['another'], 'after set 2');

foreach ($package->bundledpackage as $name => $name2) {
    $test->assertEquals($name, $name2, 'should be the same');
    $test->assertEquals('another', $name, 'another');
}

try {
    $package->bundledpackage[] = 1;
    throw new Exception('[] = 1 worked and should not');
} catch (\Pyrus\PackageFile\Exception $e) {
    $test->assertEquals('Can only set bundledpackage to string', $e->getMessage(),
        '[] = 1');
}
?>
===DONE===
--EXPECT--
===DONE===