--TEST--
Test for PEAR2\Console\CommandLine::addCommand() method.
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = new PEAR2\Console\CommandLine();
$parser->addCommand('cmd1');
$parser->addCommand('cmd2', array(
    'description' => 'description of cmd2'
));
$cmd3 = new PEAR2\Console\CommandLine\Command(array(
    'name' => 'cmd3',
    'description' => 'description of cmd3'    
));
$parser->addCommand($cmd3);

var_dump(array_keys($parser->commands));
var_dump($parser->commands['cmd2']->description);
var_dump($parser->commands['cmd3']->description);

?>
--EXPECT--
array(3) {
  [0]=>
  string(4) "cmd1"
  [1]=>
  string(4) "cmd2"
  [2]=>
  string(4) "cmd3"
}
string(19) "description of cmd2"
string(19) "description of cmd3"
