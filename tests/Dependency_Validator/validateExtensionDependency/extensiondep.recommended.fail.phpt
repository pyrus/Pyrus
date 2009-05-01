--TEST--
Dependency_Validator: Extension dependency, recommended failure
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new PEAR2_Pyrus_PackageFile_v2;
$foo = $fake->dependencies['required']->extension['foo']->recommended('2.1.0');
$validator->extensions['foo'] = true;
$validator->versions['foo'] = '2.0.0';

$test->assertEquals(false, $validator->validateExtensionDependency($foo), 'basic fail');
$test->assertEquals(1, count($errs->E_ERROR), 'basic fail count');
$test->assertEquals(1, count($errs), 'basic fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test dependency: PHP extension foo version "2.0.0" is not the recommended version "2.1.0", but may be compatible, use --force to install', $error->getMessage(),
                        'basic fail message');
}
?>
===DONE===
--EXPECT--
===DONE===