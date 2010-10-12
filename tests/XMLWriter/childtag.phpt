--TEST--
Pyrus XMLWriter: child tag
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$write = new $xmlwriter(array(
  'a' =>
  array(
    'b' => '',
  ),
));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a>
 <b/>
</a>', (string) $write, 'test');
?>
===DONE===
--EXPECT--
===DONE===