--TEST--
Pyrus XMLWriter: child tag + content
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$write = new $xmlwriter(array(
  'a' =>
  array(
    'b' => '',
    '_content' => 'hi',
  ),
));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a>
 <b/>hi
</a>', (string) $write, 'test');
?>
===DONE===
--EXPECT--
===DONE===