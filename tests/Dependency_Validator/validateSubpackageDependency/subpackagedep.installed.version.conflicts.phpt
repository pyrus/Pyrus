--TEST--
Dependency_Validator: subpackage dependency, conflicts, installed min pass
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

$foo = $fake->dependencies['required']->subpackage['pear2.php.net/foo']->min('1.3.0')->conflicts(true);

$test->assertEquals(true, $validator->validateSubpackageDependency($foo, array()), 'foo');
$test->assertEquals(0, count($errs->E_WARNING), 'foo count');
$test->assertEquals(0, count($errs), 'foo count 2');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===