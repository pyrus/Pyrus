--TEST--
Validate::validPackageName()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$validate = new \Pyrus\Validate;
$test->assertEquals(false, $validate->validPackageName('55'), '55');
$test->assertEquals(true, $validate->validPackageName('55', '55'), '55 validate package name');
$test->assertEquals(false, $validate->validPackageName('5.5'), '5.5');
$test->assertEquals(true, $validate->validPackageName('5.5', '5.5'), '5.5 validate package name');
$test->assertEquals(true, $validate->validPackageName('Vendor_Name.Package_Name'), 'Vendor_Name.Package_Name validate package name');
$test->assertEquals(false, $validate->validPackageName('With Space'), 'Package name with space');
$test->assertEquals(0, count($validate->getFailures()), 'failure count');
?>
===DONE===
--EXPECT--
===DONE===