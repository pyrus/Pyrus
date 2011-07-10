--TEST--
Dependency_Validator: uninstall package dependency, optional dependency, OK to uninstall
--FILE--
<?php
require __DIR__ . '/../setup.uninstall.php.inc';

$fake = new \Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';

$foo = $fake->dependencies['optional']->package['pear2.php.net/foo'];

$test->assertEquals(true, $validator->validatePackageUninstall($foo, $fake, array($fake)), 'foo');
$test->assertEquals(1, count($errs->E_WARNING), 'foo count');
$test->assertEquals(1, count($errs), 'foo count 2');
$test->assertEquals('"channel://pear2.php.net/foo" can be optionally used by ' .
                        'installed package channel://pear2.php.net/test', $errs->E_WARNING[0]->getMessage(), 'foo message');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===