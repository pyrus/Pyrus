--TEST--
Pyrus XMLParser: empty xml
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a/>');
$test->assertEquals(array('a' => ''), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===