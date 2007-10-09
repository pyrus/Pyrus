--TEST--
Pyrus XMLWriter: empty xml
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';
$write = new $xmlwriter(array('a' => ''));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a/>', (string) $write, 'test');
?>
===DONE===
--EXPECT--
===DONE===