--TEST--
Dependency_Validator: Package dependency, downloaded no version
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$validator = new test_Validator($package, \pear2\Pyrus\Validate::DOWNLOADING, $errs);

$fake = new \pear2\Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$foo = $fake->dependencies['required']->package['pear2.php.net/foo'];

$test->assertEquals(true, $validator->validatePackageDependency($foo, array($fake)), 'foo');
$test->assertEquals(0, count($errs), 'foo count');

?>
===DONE===
--EXPECT--
===DONE===