--TEST--
Test for PEAR2\Console\CommandLine::parse() method (subcommand).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
-v install -f foo
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = buildParser2();
$result = $parser->parse();
var_dump($result->options);
var_dump($result->command_name);
var_dump($result->command->options);

?>
--EXPECT--
array(4) {
  ["verbose"]=>
  bool(true)
  ["logfile"]=>
  NULL
  ["help"]=>
  NULL
  ["version"]=>
  NULL
}
string(7) "install"
array(2) {
  ["force"]=>
  bool(true)
  ["help"]=>
  NULL
}
