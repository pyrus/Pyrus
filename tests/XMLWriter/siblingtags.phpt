--TEST--
Pyrus XMLWriter: sibling tags
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$write = new $xmlwriter(array(
  'a' =>
  array(
    'b' => array('', ''),
  ),
));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a>
 <b></b>
 <b/>
</a>', (string) $write, 'test');
?>
===DONE===
--EXPECT--
===DONE===