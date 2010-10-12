--TEST--
Pyrus XMLParser: attributes
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a one="two"/>');
$test->assertEquals(array('a' => array('attribs' => array('one' => 'two'))), $res, 'test');
$res = $parser->parseString('<?xml version="1.0" ?><a one="two" three="four"/>');
$test->assertEquals(array('a' => array('attribs' => array('one' => 'two', 'three' => 'four'))), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===