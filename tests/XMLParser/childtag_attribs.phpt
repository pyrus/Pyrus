--TEST--
Pyrus XMLParser: child tag + attributes
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a><b one="two"/></a>');
$test->assertEquals(array(
  'a' =>
  array(
    'b' =>
    array(
      'attribs' =>
      array(
        'one' => 'two'
      )
    ),
  ),
), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===