--TEST--
Pyrus XMLParser: empty xml
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a/>');
$test->assertEquals(array('a' => ''), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===