--TEST--
Pyrus XMLWriter: attributes + content
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$write = new $xmlwriter(array(
  'a' =>
  array(
    'attribs' =>
    array(
      'one' => 'two',
    ),
    '_content' => 'hi',
  ),
));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a one="two">hi</a>', (string) $write, 'test');
?>
===DONE===
--EXPECT--
===DONE===