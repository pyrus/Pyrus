--TEST--
Dependency_Validator: Extension dependency, extension version valid bounds
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \pear2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo']->min('1.0.0')->max('1.2.3');
$validator->extensions['foo'] = true;
$validator->versions['foo'] = '1.2.3';

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo max');
$test->assertEquals(0, count($errs), 'foo count max');

$validator->versions['foo'] = '1.0.0';

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo min');
$test->assertEquals(0, count($errs), 'foo count min');

$foo->recommended('1.0.0');

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo recommended');
$test->assertEquals(0, count($errs), 'foo count recommended');

$foo->exclude('1.2.0');

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo exclude');
$test->assertEquals(0, count($errs), 'foo count exclude');

$foo->exclude = null;
$foo->exclude('1.0.0')->exclude('1.4.5')->conflicts(true);

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo conflicts with exclude');
$test->assertEquals(0, count($errs), 'foo count exclude');
?>
===DONE===
--EXPECT--
===DONE===