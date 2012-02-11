--TEST--
\Pyrus\XMLParser::parse() using schema in directory with space
--FILE--
<?php
include __DIR__ . '/../test_framework.php.inc';

// Test whether pyrus can parse XML when the schema is in a directory with a space when phar://
$phar = new Phar(__DIR__ . '/test.phar');
$phar->addEmptyDir('test dir');
$phar->addFile(__DIR__ . '/../../data/package-2.0.xsd', 'test dir/package-2.0.xsd');

$filename = 'phar://'.__DIR__.'/test.phar/test dir/package-2.0.xsd';

$xml = new Pyrus\XMLParser();
$xml->parseString(file_get_contents(__DIR__ . '/../PackageFile_Parser_v2/packages/package2.xml'), $filename);
?>
===DONE===
--CLEAN--
<?php
if (file_exists(__DIR__ . '/test.phar')) {
    unlink(__DIR__ . '/test.phar');
}
?>
--EXPECT--
===DONE===
