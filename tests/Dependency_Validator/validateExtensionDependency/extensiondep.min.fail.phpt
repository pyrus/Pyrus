--TEST--
Dependency_Validator: Extension dependency, min bounds failure
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo']->min('2.0.0');
$validator->extensions['foo'] = true;
$validator->versions['foo'] = '1.2.3';

$test->assertEquals(false, $validator->validateExtensionDependency($foo), 'basic fail');
$test->assertEquals(1, count($errs->E_ERROR), 'basic fail count');
$test->assertEquals(1, count($errs), 'basic fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PHP extension "foo" (version >= 2.0.0), installed version is 1.2.3', $error->getMessage(),
                        'basic fail message');
}

$foo->conflicts(true);

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->extensions['foo'] = true;
$validator->versions['foo'] = '2.0.0';

$test->assertEquals(false, $validator->validateExtensionDependency($foo), 'conflicts fail');
$test->assertEquals(1, count($errs->E_ERROR), 'conflicts fail count');
$test->assertEquals(1, count($errs), 'conflicts fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test conflicts with PHP extension "foo" (version >= 2.0.0), installed version is 2.0.0', $error->getMessage(),
                        'conflicts fail message');
}
?>
===DONE===
--EXPECT--
===DONE===