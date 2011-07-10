--TEST--
Dependency_Validator: package dependency, conflicts, installed min fail
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$fake = new \Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
\Pyrus\Config::current()->registry->install($fake);

$foo = $fake->dependencies['required']->package['pear2.php.net/foo']->min('1.2.2')->conflicts(true);

$test->assertEquals(false, $validator->validatePackageDependency($foo, array()), 'foo');
$test->assertEquals(1, count($errs->E_ERROR), 'foo count');
$test->assertEquals(1, count($errs), 'foo count 2');
$test->assertEquals('channel://pear2.php.net/test conflicts with package "channel://pear2.php.net/foo" (version >= 1.2.2), installed version is 1.2.3', $errs->E_ERROR[0]->getMessage(), 'foo message');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===