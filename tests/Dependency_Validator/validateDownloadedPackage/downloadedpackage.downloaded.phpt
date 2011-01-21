--TEST--
Dependency_Validator: validate downloaded package, would fail, but new downloaded version passes
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$reg = \PEAR2\Pyrus\Config::current()->registry;

$fake = new \PEAR2\Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
$fake->dependencies['optional']->package['pear2.php.net/test']->min('1.2.4');

$reg->install($fake);

$foo = clone $fake;
$fake->name = 'test';
\PEAR2\Pyrus\Logger::$log = array();
$test->assertEquals(true, $validator->validateDownloadedPackage($fake, array($fake, $foo)), 'foo');
$test->assertEquals(0, count($errs), 'foo count');
$test->assertEquals(array('skipping installed package check of "channel://pear2.php.net/foo", version "1.2.3" will be downloaded and installed'), \PEAR2\Pyrus\Logger::$log[3], 'log');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===