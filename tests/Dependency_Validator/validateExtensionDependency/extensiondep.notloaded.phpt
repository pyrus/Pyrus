--TEST--
Dependency_Validator: Extension dependency, extension version, extension not loaded
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \PEAR2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo']->min('1.0')->conflicts(true);
$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo');

$foo->conflicts = false;
$test->assertEquals(false, $validator->validateExtensionDependency($foo), 'foo fail');
$test->assertEquals(1, count($errs->E_ERROR), 'foo fail count');
$test->assertEquals(1, count($errs), 'foo fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PHP extension "foo" (version >= 1.0)', $error->getMessage(),
                        'foo fail message');
}

$foo = $fake->dependencies['optional']->extension['foo']->min('1.0');
// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo optional fail');
$test->assertEquals(1, count($errs->E_WARNING), 'foo optional fail count');
$test->assertEquals(1, count($errs), 'foo optional fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('channel://pear2.php.net/test can optionally use PHP extension "foo" (version >= 1.0)', $error->getMessage(),
                        'foo optional fail message');
}
?>
===DONE===
--EXPECT--
===DONE===