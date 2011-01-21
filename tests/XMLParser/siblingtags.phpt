--TEST--
Pyrus XMLParser: sibling tags
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$res = $parser->parseString('<?xml version="1.0" ?><a><b/><b/></a>');
$test->assertEquals(array(
  'a' =>
  array(
    'b' => array('', ''),
  ),
), $res, 'test');
?>
===DONE===
--EXPECT--
===DONE===