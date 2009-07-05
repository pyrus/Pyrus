--TEST--
Dependency_Validator: validate downloaded package, failure, optional dep not satisfied
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$reg = \pear2\Pyrus\Config::current()->registry;

$fake = new \pear2\Pyrus\PackageFile\v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';
$fake->dependencies['optional']->package['pear2.php.net/test']->min('1.2.4');

$reg->install($fake);

$fake->name = 'test';

$test->assertEquals(false, $validator->validateDownloadedPackage($fake, array($fake)), 'foo');
$test->assertEquals(2, count($errs->E_ERROR), 'foo count');
$test->assertEquals(2, count($errs), 'foo count 2');
$test->assertEquals('channel://pear2.php.net/foo requires package "channel://pear2.php.net/test" (version >= 1.2.4), downloaded version is 1.2.3', $errs->E_ERROR[0]->getMessage(), 'foo message 1');
$test->assertEquals('channel://pear2.php.net/test cannot be installed, conflicts with installed packages', $errs->E_ERROR[1]->getMessage(), 'foo message 2');
?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===