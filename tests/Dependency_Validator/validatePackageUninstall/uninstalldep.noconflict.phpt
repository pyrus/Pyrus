--TEST--
Dependency_Validator: uninstall package dependency, no version conflict, OK to uninstall
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

$foo = $fake->dependencies['required']->package['pear2.php.net/foo']->min('1.2.4')->max('1.3.0');

$test->assertEquals(true, $validator->validatePackageUninstall($foo, $fake, array($fake)), 'foo');
$test->assertEquals(0, count($errs), 'foo count');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===