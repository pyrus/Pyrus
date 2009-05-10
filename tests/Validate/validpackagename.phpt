--TEST--
Validate::validPackageName()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$validate = new PEAR2_Pyrus_Validate;
$test->assertEquals(false, $validate->validPackageName('55'), '55');
$test->assertEquals(true, $validate->validPackageName('55', '55'), '55 validate package name');
$test->assertEquals(false, $validate->validPackageName('5.5'), '5.5');
$test->assertEquals(true, $validate->validPackageName('5.5', '5.5'), '5.5 validate package name');
$test->assertEquals(0, count($validate->getFailures()), 'failure count');
?>
===DONE===
--EXPECT--
===DONE===