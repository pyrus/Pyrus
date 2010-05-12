--TEST--
Dependency_Validator: Extension dependency, no extension version --nodeps
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

\PEAR2\Pyrus\Main::$options['nodeps'] = true;
$fake = new \PEAR2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo'];
$validator->extensions['foo'] = true;
$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo');

$foo->conflicts = true;
$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo conflicts fail');
$test->assertEquals(1, count($errs->E_WARNING), 'foo conflicts fail count');
$test->assertEquals(1, count($errs), 'foo conflicts fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test conflicts with PHP extension "foo"', $error->getMessage(),
                        'foo conflicts fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo conflicts');

$foo->conflicts = false;
$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'foo fail');
$test->assertEquals(1, count($errs->E_WARNING), 'foo fail count');
$test->assertEquals(1, count($errs), 'foo fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test requires PHP extension "foo"', $error->getMessage(),
                        'foo fail message');
}
?>
===DONE===
--EXPECT--
===DONE===