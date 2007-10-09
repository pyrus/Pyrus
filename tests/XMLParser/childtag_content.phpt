--TEST--
Pyrus XMLParser: child tag + content
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a><b/>hi</a>');
$test->assertEquals(array(
  'a' =>
  array(
    'b' => '',
    '_content' => 'hi',
  ),
), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===