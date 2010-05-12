--TEST--
Dependency_Validator: subpackage dependency, installed recommended version fail
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$fake = new \PEAR2\Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
\PEAR2\Pyrus\Config::current()->registry->install($fake);

$foo = $fake->dependencies['required']->subpackage['pear2.php.net/foo']->recommended('1.2.2');

$test->assertEquals(false, $validator->validateSubpackageDependency($foo, array()), 'foo');
$test->assertEquals(1, count($errs->E_ERROR), 'foo count');
$test->assertEquals(1, count($errs), 'foo count 2');
$test->assertEquals('channel://pear2.php.net/test dependency package "channel://pear2.php.net/foo" installed version 1.2.3 is not the recommended version 1.2.2, but may be compatible, use --force to install', $errs->E_ERROR[0]->getMessage(), 'foo message');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===