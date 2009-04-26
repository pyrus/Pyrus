--TEST--
Registry base: test ArrayAccess
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.channels.template';

$test->assertEquals(true, isset($reg->package['pear2.php.net/HooHa']), 'offsetExists');
$test->assertEquals(true, isset($reg->package['pear/HooHa2']), 'short offsetExists');
$test->assertEquals(false, isset($reg->package['pear2/Nonexisting']), 'non-existing offsetExists');

$package = $reg->package['pear2.php.net/HooHa'];
unset($reg->package['pear2.php.net/HooHa']);
$test->assertEquals(false, isset($reg->package['pear2.php.net/HooHa']), 'offsetUnset');
$reg->package[] = $package;
$test->assertEquals(true, isset($reg->package['pear2.php.net/HooHa']), 'offsetSet');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===