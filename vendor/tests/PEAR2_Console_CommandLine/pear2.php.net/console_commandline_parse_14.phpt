--TEST--
Test for PEAR2\Console\CommandLine::parse() method (user errors 2).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
--int=foo foo bar
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = buildParser1();
try {
    $result = $parser->parse();
} catch (PEAR2\Console\CommandLine\Exception $exc) {
    echo $exc->getMessage();
}

?>
--EXPECT--
Option "int" requires a value of type int (got "foo").
