--TEST--
Dependency_Validator: OS dependency unix
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \Pyrus\PackageFile\v2;
$os = $fake->dependencies['required']->os;
$os->name = 'unix';
$validator->os = $validator->sysname = 'Linux';
$test->assertEquals(true, $validator->validateOSDependency($os), 'unix pass');

$os->conflicts = true;
$test->assertEquals(false, $validator->validateOSDependency($os), 'unix conflicts fail');
$test->assertEquals(1, count($errs->E_ERROR), 'unix conflicts fail count');
$test->assertEquals(1, count($errs), 'unix conflicts fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('Cannot install channel://pear2.php.net/test on any Unix system', $error->getMessage(),
                        'unix conflicts fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->os = $validator->sysname = 'Linux';
$os->name = 'linux';
$os->conflicts = null;
$test->assertEquals(true, $validator->validateOSDependency($os), 'linux pass');

$os->conflicts = true;
$test->assertEquals(false, $validator->validateOSDependency($os), 'linux conflicts fail');
$test->assertEquals(1, count($errs->E_ERROR), 'linux conflicts fail count');
$test->assertEquals(1, count($errs), 'linux conflicts fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('Cannot install channel://pear2.php.net/test on linux operating system', $error->getMessage(),
                        'linux conflicts fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$os->name = 'unix';
$os->conflicts = true;
$validator->os = $validator->sysname = 'Windows XP';
$test->assertEquals(true, $validator->validateOSDependency($os), 'unix conflicts pass');

$os->conflicts = null;
$test->assertEquals(false, $validator->validateOSDependency($os), 'unix fail');
$test->assertEquals(1, count($errs->E_ERROR), 'unix fail count');
$test->assertEquals(1, count($errs), 'unix fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('Can only install channel://pear2.php.net/test on a Unix system', $error->getMessage(),
                        'unix fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$os->name = 'linux';
$os->conflicts = true;
$validator->os = $validator->sysname = 'Windows XP';
$test->assertEquals(true, $validator->validateOSDependency($os), 'linux conflicts pass');

$os->conflicts = null;
$test->assertEquals(false, $validator->validateOSDependency($os), 'linux fail');
$test->assertEquals(1, count($errs->E_ERROR), 'linux fail count');
$test->assertEquals(1, count($errs), 'linux fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('Cannot install channel://pear2.php.net/test on Windows XP operating system, can only install on linux', $error->getMessage(),
                        'linux fail message');
}
?>
===DONE===
--EXPECT--
===DONE===