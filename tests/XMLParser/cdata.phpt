--TEST--
Pyrus XMLParser: <![CDATA[]]>
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a><![CDATA[hi<there>]]></a>');
$test->assertEquals(array('a' => 'hi<there>'), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===