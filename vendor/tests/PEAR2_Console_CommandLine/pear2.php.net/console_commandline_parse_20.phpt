--TEST--
Test for PEAR2\Console\CommandLine::fromXmlFile() method.
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
--list-choice
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = PEAR2\Console\CommandLine::fromXmlFile(__DIR__ . DIRECTORY_SEPARATOR . 'test.xml');
$parser->parse();

?>
--EXPECTF--
Valid choices are: ham, spam
