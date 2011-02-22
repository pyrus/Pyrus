--TEST--
Test for PEAR2\Console\CommandLine::addOption() method (errors 6).
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = new PEAR2\Console\CommandLine();
$parser->addOption('name', array('short_name'=>'-d', 'action'=>'Inexistant'));

?>
--EXPECTF--

Fatal error: unregistered action "Inexistant" for option "name". in %sCommandLine.php on line %d
