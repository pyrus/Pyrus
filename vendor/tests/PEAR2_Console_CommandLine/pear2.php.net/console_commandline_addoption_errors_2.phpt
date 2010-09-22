--TEST--
Test for PEAR2\Console\CommandLine::addOption() method (errors 2).
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = new PEAR2\Console\CommandLine();
$parser->addOption('name', array());

?>
--EXPECTF--

Fatal error: you must provide at least an option short name or long name for option "name" in %sCommandLine.php on line %d
