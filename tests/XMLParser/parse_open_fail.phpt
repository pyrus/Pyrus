--TEST--
Pyrus XMLParser: parse() opening file fails
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$e = new stdClass;
try {
    $res = $parser->parse('/path/to/random/file.xml');
} catch (Pyrus\XMLParser\Exception $e) {}

$test->assertEquals('Cannot open /path/to/random/file.xml for parsing', $e->getMessage(), 'error');
?>
===DONE===
--EXPECT--
===DONE===