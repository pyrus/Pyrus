--TEST--
Dependency_Validator: uninstall package dependency, no version conflict because of exclude, OK to uninstall (2)
--FILE--
<?php
require __DIR__ . '/../setup.uninstall.php.inc';

$fake = new \PEAR2\Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';

$foo = $fake->dependencies['required']->package['pear2.php.net/foo']->exclude('1.2.3');

$test->assertEquals(true, $validator->validatePackageUninstall($foo, $fake, array($fake)), 'foo');
$test->assertEquals(0, count($errs), 'foo count');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===