--TEST--
Dependency_Validator: package dependency, no version, installed failure
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$fake = new PEAR2_Pyrus_PackageFile_v2;
$foo = $fake->dependencies['required']->package['pear2.php.net/foo'];

$test->assertEquals(false, $validator->validatePackageDependency($foo, array()), 'foo');
$test->assertEquals(1, count($errs->E_ERROR), 'foo count');
$test->assertEquals(1, count($errs), 'foo count 2');
$test->assertEquals('channel://pear2.php.net/test requires package "channel://pear2.php.net/foo"', $errs->E_ERROR[0]->getMessage(), 'foo error');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===