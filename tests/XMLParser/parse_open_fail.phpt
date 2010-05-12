--TEST--
Pyrus XMLParser: parse() opening file fails
--FILE--
<?php
require dirname(__FILE__) . '/setup.php.inc';

$e = new stdClass;
try {
    $res = $parser->parse('/path/to/random/file.xml');
} catch (PEAR2\Pyrus\XMLParser\Exception $e) {}

$test->assertEquals('Cannot open /path/to/random/file.xml for parsing', $e->getMessage(), 'error');
?>
===DONE===
--EXPECT--
===DONE===