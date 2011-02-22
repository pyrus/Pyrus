--TEST--
Test for PEAR2\Console\CommandLine::addOption() method (errors 4).
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = new PEAR2\Console\CommandLine();
$parser->addOption('name', array('long_name'=>'d'));

?>
--EXPECTF--

Fatal error: option "name" long name must be 2 dashes followed by a word (got: "d") in %sCommandLine.php on line %d
