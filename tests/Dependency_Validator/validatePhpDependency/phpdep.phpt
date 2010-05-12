--TEST--
Dependency_Validator: PHP dependency
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \PEAR2\Pyrus\PackageFile\v2;
$php = $fake->dependencies['required']->php;
$php->min('5.3.0')->max('5.4.0')->exclude('5.3.1');
$test->assertEquals(true, $validator->validatePhpDependency($php), '5.3.0');

$validator->phpversion = '5.4.0';
$test->assertEquals(true, $validator->validatePhpDependency($php), '5.4.0');

$validator->phpversion = '5.2.9';
$test->assertEquals(false, $validator->validatePhpDependency($php), '5.2.9 fail');
$test->assertEquals(1, count($errs->E_ERROR), '5.2.9 fail count');
$test->assertEquals(1, count($errs), '5.2.9 fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PHP ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.2.9', $error->getMessage(),
                        '5.2.9 fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->phpversion = '5.4.1';
$test->assertEquals(false, $validator->validatePhpDependency($php), '5.4.1 fail');
$test->assertEquals(1, count($errs->E_ERROR), '5.4.1 fail count');
$test->assertEquals(1, count($errs), '5.4.1 fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test requires PHP ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.4.1', $error->getMessage(),
                        '5.4.1 fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->phpversion = '5.3.1';
$test->assertEquals(false, $validator->validatePhpDependency($php), '5.3.1 fail');
$test->assertEquals(1, count($errs->E_ERROR), '5.3.1 fail count');
$test->assertEquals(1, count($errs), '5.3.1 fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test is not compatible with PHP version 5.3.1', $error->getMessage(),
                        '5.3.1 fail message');
}

\PEAR2\Pyrus\Main::$options = array('force' => true);

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$validator->phpversion = '5.2.9';
$test->assertEquals(true, $validator->validatePhpDependency($php), '5.2.9 fail');
$test->assertEquals(1, count($errs->E_WARNING), '5.2.9 fail count');
$test->assertEquals(1, count($errs), '5.2.9 fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test requires PHP ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.2.9', $error->getMessage(),
                        '5.2.9 fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->phpversion = '5.4.1';
$test->assertEquals(true, $validator->validatePhpDependency($php), '5.4.1 fail');
$test->assertEquals(1, count($errs->E_WARNING), '5.4.1 fail count');
$test->assertEquals(1, count($errs), '5.4.1 fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test requires PHP ' .
                        '(version >= 5.3.0, version <= 5.4.0, excluded versions: 5.3.1)' .
                        ', installed version is 5.4.1', $error->getMessage(),
                        '5.4.1 fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);

$validator->phpversion = '5.3.1';
$test->assertEquals(true, $validator->validatePhpDependency($php), '5.3.1 fail');
$test->assertEquals(1, count($errs->E_WARNING), '5.3.1 fail count');
$test->assertEquals(1, count($errs), '5.3.1 fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test is not compatible with PHP version 5.3.1', $error->getMessage(),
                        '5.3.1 fail message');
}

// reset multierrors
$errs = new \PEAR2\MultiErrors;
$validator = new test_Validator($package, \PEAR2\Pyrus\Validate::UNINSTALLING, $errs);

$validator->phpversion = '5.3.1';
$test->assertEquals(true, $validator->validatePhpDependency($php), 'UNINSTALLING should not fail on conflict');
$test->assertEquals(0, count($errs), 'UNINSTALLING fail count');

?>
===DONE===
--EXPECT--
===DONE===