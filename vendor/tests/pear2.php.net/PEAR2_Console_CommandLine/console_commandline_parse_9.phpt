--TEST--
Test for PEAR2\Console\CommandLine::parse() method (password option).
--SKIPIF--
<?php if(php_sapi_name()!='cli') echo 'skip'; ?>
--ARGS--
-p -- foo bar
--STDIN--
secretpass
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = buildParser1();
$result = $parser->parse();
var_dump($result->options);
var_dump($result->args);

?>
--EXPECT--
Password: array(11) {
  ["true"]=>
  NULL
  ["false"]=>
  NULL
  ["int"]=>
  int(1)
  ["float"]=>
  float(1)
  ["string"]=>
  NULL
  ["counter"]=>
  NULL
  ["callback"]=>
  NULL
  ["array"]=>
  array(2) {
    [0]=>
    string(4) "spam"
    [1]=>
    string(3) "egg"
  }
  ["password"]=>
  string(10) "secretpass"
  ["help"]=>
  NULL
  ["version"]=>
  NULL
}
array(2) {
  ["simple"]=>
  string(3) "foo"
  ["multiple"]=>
  array(1) {
    [0]=>
    string(3) "bar"
  }
}
