--TEST--
Dependency_Validator: validate downloaded package, simple case, optionally depends
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$reg = \Pyrus\Config::current()->registry;

$fake = new \Pyrus\PackageFile\v2;
$fake->name = 'test';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
$fake->dependencies['optional']->package['pear2.php.net/foo']->save();

$reg->install($fake);

$fake->name = 'foo';

$test->assertEquals(true, $validator->validateDownloadedPackage($fake, array($fake)), 'foo');
$test->assertEquals(0, count($errs), 'foo count');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===