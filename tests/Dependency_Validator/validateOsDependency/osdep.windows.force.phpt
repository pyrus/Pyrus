--TEST--
Dependency_Validator: OS dependency windows --force
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
PEAR2_Pyrus_Installer::$options = array('force' => true);
$fake = new PEAR2_Pyrus_PackageFile_v2;
$os = $fake->dependencies['required']->os;
$os->name = 'windows';
$validator->os = 'Windows XP';
$test->assertEquals(true, $validator->validateOSDependency($os), 'windows pass');

$os->conflicts = true;
$test->assertEquals(true, $validator->validateOSDependency($os), 'windows conflicts fail');
$test->assertEquals(1, count($errs->E_WARNING), 'windows conflicts fail count');
$test->assertEquals(1, count($errs), 'windows conflicts fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: Cannot install channel://pear2.php.net/test on Windows', $error->getMessage(),
                        'windows conflicts fail message');
}

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->os = 'Linux';
$test->assertEquals(true, $validator->validateOSDependency($os), 'windows conflicts pass');

$os->conflicts = false;
$test->assertEquals(true, $validator->validateOSDependency($os), 'windows fail');
$test->assertEquals(1, count($errs->E_WARNING), 'windows fail count');
$test->assertEquals(1, count($errs), 'windows fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: Can only install channel://pear2.php.net/test on Windows', $error->getMessage(),
                        'windows fail message');
}
?>
===DONE===
--EXPECT--
===DONE===