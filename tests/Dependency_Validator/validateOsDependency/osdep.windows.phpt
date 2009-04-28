--TEST--
Dependency_Validator: OS dependency windows
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$fake = new PEAR2_Pyrus_PackageFile_v2;
$os = $fake->dependencies['required']->os;
$os->name = 'windows';
$validator->os = 'Windows XP';
$test->assertEquals(true, $validator->validateOSDependency($os), 'windows pass');

$os->conflicts = true;
$test->assertEquals(false, $validator->validateOSDependency($os), 'windows conflicts fail');
$test->assertEquals(1, count($errs->E_ERROR), 'windows conflicts fail count');
$test->assertEquals(1, count($errs), 'windows conflicts fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('Cannot install pear2.php.net/test on Windows', $error->getMessage(),
                        'windows conflicts fail message');
}

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->os = 'Linux';
$test->assertEquals(true, $validator->validateOSDependency($os), 'windows conflicts pass');

$os->conflicts = null;
$test->assertEquals(false, $validator->validateOSDependency($os), 'windows fail');
$test->assertEquals(1, count($errs->E_ERROR), 'windows fail count');
$test->assertEquals(1, count($errs), 'windows fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('Can only install pear2.php.net/test on Windows', $error->getMessage(),
                        'windows fail message');
}
?>
===DONE===
--EXPECT--
===DONE===