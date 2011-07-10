--TEST--
Dependency_Validator: subpackage dependency, installed recommended version
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$validator = new test_Validator($package, \Pyrus\Validate::DOWNLOADING, $errs);

$fake = new \Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
$fake->compatible['pear2.php.net/test']->min('1.2.2')->max('1.2.3');
\Pyrus\Config::current()->registry->install($fake);

$fake->name = 'test';

$foo = $fake->dependencies['required']->subpackage['pear2.php.net/foo']->recommended('1.2.2');

$test->assertEquals(true, $validator->validateSubpackageDependency($foo, array($fake)), 'foo');
$test->assertEquals(0, count($errs), 'foo count');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===