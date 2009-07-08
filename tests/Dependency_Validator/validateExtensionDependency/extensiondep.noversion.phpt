--TEST--
Dependency_Validator: Extension dependency, no extension version
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \pear2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo'];
$validator->extensions['foo'] = true;
$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo');

$foo->conflicts = true;
$test->assertEquals(false, $validator->validateExtensionDependency($foo), 'foo conflicts fail');
$test->assertEquals(1, count($errs->E_ERROR), 'foo conflicts fail count');
$test->assertEquals(1, count($errs), 'foo conflicts fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test conflicts with PHP extension "foo"', $error->getMessage(),
                        'foo conflicts fail message');
}

// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo conflicts');

$foo->conflicts = false;
$test->assertEquals(false, $validator->validateExtensionDependency($foo), 'foo fail');
$test->assertEquals(1, count($errs->E_ERROR), 'foo fail count');
$test->assertEquals(1, count($errs), 'foo fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PHP extension "foo"', $error->getMessage(),
                        'foo fail message');
}

// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$foo = $fake->dependencies['optional']->extension['foo'];
$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo optional fail');
$test->assertEquals(1, count($errs->E_WARNING), 'foo fail count');
$test->assertEquals(1, count($errs), 'foo fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('channel://pear2.php.net/test can optionally use PHP extension "foo"', $error->getMessage(),
                        'foo optional fail message');
}
?>
===DONE===
--EXPECT--
===DONE===