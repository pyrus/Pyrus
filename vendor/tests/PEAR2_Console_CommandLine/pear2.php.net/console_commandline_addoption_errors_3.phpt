--TEST--
Test for PEAR2\Console\CommandLine::addOption() method (errors 3).
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = new PEAR2\Console\CommandLine();
$parser->addOption('name', array('short_name'=>'d'));

?>
--EXPECTF--

Fatal error: option "name" short name must be a dash followed by a letter (got: "d") in %sCommandLine.php on line %d
