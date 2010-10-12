--TEST--
Pyrus XMLWriter: child tag + sibling attributes
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$write = new $xmlwriter(array(
  'a' =>
  array(
    'b' =>
    array(
      '',
      array(
        'attribs' =>
        array(
          'one' => 'two'
        )
      ),
    ),
  ),
));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a>
 <b></b>
 <b one="two"/>
</a>', (string) $write, 'test');
?>
===DONE===
--EXPECT--
===DONE===