--TEST--
Dependency_Validator: subpackage dependency, conflicts, not installed
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$fake = new \PEAR2\Pyrus\PackageFile\v2;
$foo = $fake->dependencies['required']->subpackage['pear2.php.net/foo']->min('1.2.0')->conflicts(true);

$test->assertEquals(true, $validator->validateSubpackageDependency($foo, array()), 'foo');
$test->assertEquals(0, count($errs->E_WARNING), 'foo count');
$test->assertEquals(0, count($errs), 'foo count 2');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===