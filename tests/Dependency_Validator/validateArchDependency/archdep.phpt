--TEST--
Dependency_Validator: arch dependency
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$fake = new \pear2\Pyrus\PackageFile\v2;
$arch = $fake->dependencies['required']->arch;
$arch->pattern = 'foobar';
$validator->patterns['foobar'] = true;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'foobar pass');

$arch->conflicts = true;
$test->assertEquals(false, $validator->validateArchDependency($arch), 'foobar conflicts fail');
$test->assertEquals(1, count($errs->E_ERROR), 'foobar conflicts fail count');
$test->assertEquals(1, count($errs), 'foobar conflicts fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test Architecture dependency failed, cannot match ' .
                        '"foobar"', $error->getMessage(),
                        'foobar conflicts fail message');
}

\pear2\Pyrus\Main::$options = array('force' => true);
// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$arch->conflicts = false;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'foobar pass force');

$arch->conflicts = true;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'foobar conflicts fail force');
$test->assertEquals(1, count($errs->E_WARNING), 'foobar conflicts fail count');
$test->assertEquals(1, count($errs), 'foobar conflicts fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test Architecture dependency failed, does not ' .
                        'match "foobar"', $error->getMessage(),
                        'foobar conflicts fail message force');
}

\pear2\Pyrus\Main::$options = array();
// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$arch->conflicts = true;
$arch->pattern = 'barfoo';
$test->assertEquals(true, $validator->validateArchDependency($arch), 'barfoo conflicts pass');

$arch->conflicts = false;
$test->assertEquals(false, $validator->validateArchDependency($arch), 'barfoo fail');
$test->assertEquals(1, count($errs->E_ERROR), 'barfoo conflicts fail count');
$test->assertEquals(1, count($errs), 'barfoo conflicts fail count 2');
foreach ($errs->E_ERROR as $error) {
    $test->assertEquals('channel://pear2.php.net/test Architecture dependency failed, does not match ' .
                        '"barfoo"', $error->getMessage(),
                        'barfoo fail message');
}

\pear2\Pyrus\Main::$options = array('nodeps' => true);
// reset multierrors
$errs = new \pear2\MultiErrors;
$validator = new test_Validator($package, $state, $errs);
$arch->conflicts = true;
$arch->pattern = 'barfoo';
$test->assertEquals(true, $validator->validateArchDependency($arch), 'barfoo conflicts pass nodeps');

$validator->patterns['barfoo'] = true;
$test->assertEquals(true, $validator->validateArchDependency($arch), 'barfoo fail nodeps');
$test->assertEquals(1, count($errs->E_WARNING), 'barfoo conflicts fail count');
$test->assertEquals(1, count($errs), 'barfoo conflicts fail count 2');
foreach ($errs->E_WARNING as $error) {
    $test->assertEquals('warning: channel://pear2.php.net/test Architecture dependency failed, cannot match ' .
                        '"barfoo"', $error->getMessage(),
                        'barfoo fail message nodeps');
}
?>
===DONE===
--EXPECT--
===DONE===