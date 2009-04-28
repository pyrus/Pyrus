--TEST--
Dependency_Validator: Pear installer dependency
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new PEAR2_Pyrus_PackageFile_v2;
$pear = $fake->dependencies['required']->pearinstaller;
$pear->min('5.3.0')->max('5.4.0')->exclude('5.3.1');
$validator->pearversion = '5.3.0';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.3.0');

$validator->pearversion = '5.4.0';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.4.0');

$validator->pearversion = '5.2.9';
$test->assertEquals(false, $validator->validatePearinstallerDependency($pear), '5.2.9 fail');
$test->assertEquals(1, count($errs), '5.2.9 fail count');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.2.9', $error->getMessage(),
                        '5.2.9 fail message');
}

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.4.1';
$test->assertEquals(false, $validator->validatePearinstallerDependency($pear), '5.4.1 fail');
$test->assertEquals(1, count($errs), '5.4.1 fail count');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.4.1', $error->getMessage(),
                        '5.4.1 fail message');
}

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.3.1';
$test->assertEquals(false, $validator->validatePearinstallerDependency($pear), '5.3.1 fail');
$test->assertEquals(1, count($errs), '5.3.1 fail count');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('pear2.php.net/test is not compatible with PEAR Installer version 5.3.1', $error->getMessage(),
                        '5.3.1 fail message');
}

PEAR2_Pyrus_Installer::$options = array('force' => true);

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->pearversion = '5.2.9';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.2.9 fail');
$test->assertEquals(1, count($errs), '5.2.9 fail count');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.2.9', $error->getMessage(),
                        '5.2.9 fail message');
}

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.4.1';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.4.1 fail');
$test->assertEquals(1, count($errs), '5.4.1 fail count');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.4.1', $error->getMessage(),
                        '5.4.1 fail message');
}

// reset multierrors
$errs = new PEAR2_MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.3.1';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.3.1 fail');
$test->assertEquals(1, count($errs), '5.3.1 fail count');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: pear2.php.net/test is not compatible with PEAR Installer version 5.3.1', $error->getMessage(),
                        '5.3.1 fail message');
}

?>
===DONE===
--EXPECT--
===DONE===