--TEST--
Pyrus XMLWriter: empty xml
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$write = new $xmlwriter(array('a' => ''));
$test->assertEquals('<?xml version="1.0" encoding="UTF-8"?>
<a/>', (string) $write, 'test');

$e = new stdClass;
try {
    $write = new $xmlwriter(array());
} catch (Pyrus\XMLWriter\Exception $e) {}
$test->assertEquals('Cannot serialize array to XML, array must have exactly 1 element', $e->getMessage(), 'error');

?>
===DONE===
--EXPECT--
===DONE===