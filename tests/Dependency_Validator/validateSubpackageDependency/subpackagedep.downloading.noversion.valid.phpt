--TEST--
Dependency_Validator: Subpackage dependency, downloaded no version
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$validator = new test_Validator($package, \Pyrus\Validate::DOWNLOADING, $errs);

$fake = new \Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$foo = $fake->dependencies['required']->subpackage['pear2.php.net/foo'];

$test->assertEquals(true, $validator->validateSubpackageDependency($foo, array($fake)), 'foo');
$test->assertEquals(0, count($errs), 'foo count');

?>
===DONE===
--EXPECT--
===DONE===