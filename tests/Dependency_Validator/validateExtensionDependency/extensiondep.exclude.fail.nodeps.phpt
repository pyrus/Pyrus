--TEST--
Dependency_Validator: Extension dependency, exclude failure --nodeps
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

\pear2\Pyrus\Main::$options['nodeps'] = true;
$fake = new \pear2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo']->exclude('2.0.0');
$validator->extensions['foo'] = true;
$validator->versions['foo'] = '2.0.0';

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'basic fail');
$test->assertEquals(1, count($errs->E_WARNING), 'basic fail count');
$test->assertEquals(1, count($errs), 'basic fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test is not compatible with version 2.0.0 of PHP extension "foo", installed version is 2.0.0', $error->getMessage(),
                        'basic fail message');
}

$foo->conflicts(true);

// reset multierrors
$errs = new \PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->extensions['foo'] = true;
$validator->versions['foo'] = '2.0.1';

$test->assertEquals(true, $validator->validateExtensionDependency($foo), 'conflicts fail');
$test->assertEquals(1, count($errs->E_WARNING), 'conflicts fail count');
$test->assertEquals(1, count($errs), 'conflicts fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test is not compatible with version 2.0.1 of PHP extension "foo", installed version is 2.0.1', $error->getMessage(),
                        'conflicts fail message');
}
?>
===DONE===
--EXPECT--
===DONE===