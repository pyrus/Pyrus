--TEST--
Dependency_Validator: Pear installer dependency
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \pear2\Pyrus\PackageFile\v2;
$pear = $fake->dependencies['required']->pearinstaller;
$pear->min('5.3.0')->max('5.4.0')->exclude('5.3.1');
$validator->pearversion = '5.3.0';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.3.0');

$validator->pearversion = '5.4.0';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.4.0');

$validator->pearversion = '5.2.9';
$test->assertEquals(false, $validator->validatePearinstallerDependency($pear), '5.2.9 fail');
$test->assertEquals(1, count($errs->E_ERROR), '5.2.9 fail count');
$test->assertEquals(1, count($errs), '5.2.9 fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.2.9', $error->getMessage(),
                        '5.2.9 fail message');
}

// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.4.1';
$test->assertEquals(false, $validator->validatePearinstallerDependency($pear), '5.4.1 fail');
$test->assertEquals(1, count($errs->E_ERROR), '5.4.1 fail count');
$test->assertEquals(1, count($errs), '5.4.1 fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.4.1', $error->getMessage(),
                        '5.4.1 fail message');
}

// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.3.1';
$test->assertEquals(false, $validator->validatePearinstallerDependency($pear), '5.3.1 fail');
$test->assertEquals(1, count($errs->E_ERROR), '5.3.1 fail count');
$test->assertEquals(1, count($errs), '5.3.1 fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test is not compatible with PEAR Installer version 5.3.1', $error->getMessage(),
                        '5.3.1 fail message');
}

\pear2\Pyrus\Main::$options = array('force' => true);

// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->pearversion = '5.2.9';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.2.9 fail');
$test->assertEquals(1, count($errs->E_WARNING), '5.2.9 fail count');
$test->assertEquals(1, count($errs), '5.2.9 fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.2.9', $error->getMessage(),
                        '5.2.9 fail message');
}

// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.4.1';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.4.1 fail');
$test->assertEquals(1, count($errs->E_WARNING), '5.4.1 fail count');
$test->assertEquals(1, count($errs), '5.4.1 fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test requires PEAR Installer ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.4.1', $error->getMessage(),
                        '5.4.1 fail message');
}

// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->pearversion = '5.3.1';
$test->assertEquals(true, $validator->validatePearinstallerDependency($pear), '5.3.1 fail');
$test->assertEquals(1, count($errs->E_WARNING), '5.3.1 fail count');
$test->assertEquals(1, count($errs), '5.3.1 fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test is not compatible with PEAR Installer version 5.3.1', $error->getMessage(),
                        '5.3.1 fail message');
}

?>
===DONE===
--EXPECT--
===DONE===