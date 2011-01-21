--TEST--
Dependency_Validator: uninstall package dependency, fail uninstall, max version failure --nodeps
--FILE--
<?php
require __DIR__ . '/../setup.uninstall.php.inc';

\PEAR2\Pyrus\Main::$options['nodeps'] = true;
$fake = new \PEAR2\Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';

$foo = $fake->dependencies['required']->package['pear2.php.net/foo']->max('1.3.0');

$test->assertEquals(true, $validator->validatePackageUninstall($foo, $fake, array($fake)), 'foo');
$test->assertEquals(1, count($errs->E_WARNING), 'foo count');
$test->assertEquals(1, count($errs), 'foo count 2');
$test->assertEquals('warning: channel://pear2.php.net/foo (version <= 1.3.0) is required by installed package "channel://pear2.php.net/test"', $errs->E_WARNING[0]->getMessage(), 'foo message');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===