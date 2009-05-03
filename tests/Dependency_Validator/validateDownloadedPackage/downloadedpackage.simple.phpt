--TEST--
Dependency_Validator: validate downloaded package, simple case
--FILE--
<?php
require __DIR__ . '/../setup.registry.php.inc';

$fake = new PEAR2_Pyrus_PackageFile_v2;
$fake->name = 'foo';
$fake->channel = 'pear2.php.net';
$fake->version['release'] = '1.2.3';
$fake->files['foo'] = array('role' => 'php');
$fake->notes = 'hi';
$fake->summary = 'hi';
$fake->description = 'hi';

$test->assertEquals(true, $validator->validateDownloadedPackage($fake, array($fake)), 'foo');
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