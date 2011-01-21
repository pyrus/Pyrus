--TEST--
Pyrus XMLWriter: attributes
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$write = new $xmlwriter(array('a' => array('attribs' => array('one' => 'two'))));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a one="two"/>', (string) $write, 1);
$write = new $xmlwriter(array('a' => array('attribs' => array('one' => 'two', 'three' => 'four'))));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a one="two" three="four"/>', (string) $write, 2);
?>
===DONE===
--EXPECT--
===DONE===