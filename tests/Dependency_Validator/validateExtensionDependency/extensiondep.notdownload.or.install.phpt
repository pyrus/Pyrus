--TEST--
Dependency_Validator: Extension dependency, not downloading or installing
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \pear2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo']->exclude('2.0.0');

$validator = new test_Validator($package, \pear2\Pyrus\Validate::UNINSTALLING, $errs);
$validator->extensions['foo'] = true;
$validator->versions['foo'] = '2.0.1';

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'conflicts fail');
$test->assertEquals(0, count($errs), 'conflicts fail count 2');
?>
===DONE===
--EXPECT--
===DONE===