--TEST--
Pyrus XMLParser: attributes + content
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a one="two">hi</a>');
$test->assertEquals(array(
  'a' =>
  array(
    'attribs' =>
    array(
      'one' => 'two',
    ),
    '_content' => 'hi',
  ),
), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===