--TEST--
Dependency_Validator: Extension dependency, extension version, extension has no version
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \pear2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->extension['foo']->min('1.0');
$validator->extensions['foo'] = true;

$test->assertEquals(false, $validator->validateExtensionDependency($foo), 'foo fail');
$test->assertEquals(1, count($errs->E_ERROR), 'foo fail count');
$test->assertEquals(1, count($errs), 'foo fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PHP extension "foo" (version >= 1.0), installed version is 0', $error->getMessage(),
                        'foo fail message');
}
?>
===DONE===
--EXPECT--
===DONE===