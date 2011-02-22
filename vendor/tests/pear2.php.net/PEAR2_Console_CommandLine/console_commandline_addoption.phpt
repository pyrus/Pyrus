--TEST--
Test for PEAR2\Console\CommandLine::addOption() method.
--FILE--
<?php

require_once __DIR__ . DIRECTORY_SEPARATOR . 'tests.inc.php';

$parser = new PEAR2\Console\CommandLine();
$parser->addOption('opt1', array(
    'short_name' => '-a'
));
$parser->addOption('opt2', array(
    'short_name' => '-b',
    'long_name' => '--foo',
    'description' => 'description of opt2',
    'action' => 'StoreInt',
    'help_name' => 'bar',
    'choices' => array(1, 2, 3),
    'add_list_option' => true,
    'default' => 2
));
$opt3 = new PEAR2\Console\CommandLine\Option('opt3', array(
    'long_name' => '--bar',
    'description' => 'description of opt3',
));
$parser->addOption($opt3);

var_dump($parser->options);

?>
--EXPECTF--
array(4) {
  ["opt1"]=>
  object(PEAR2\Console\CommandLine\Option)#6 (14) {
    ["short_name"]=>
    string(2) "-a"
    ["long_name"]=>
    NULL
    ["action"]=>
    string(11) "StoreString"
    ["default"]=>
    NULL
    ["choices"]=>
    array(0) {
    }
    ["callback"]=>
    NULL
    ["action_params"]=>
    array(0) {
    }
    ["argument_optional"]=>
    bool(false)
    ["add_list_option"]=>
    bool(false)
    ["_action_instance":"PEAR2\Console\CommandLine\Option":private]=>
    NULL
    ["name"]=>
    string(4) "opt1"
    ["help_name"]=>
    string(4) "opt1"
    ["description"]=>
    NULL
    ["messages"]=>
    array(0) {
    }
  }
  ["opt2"]=>
  object(PEAR2\Console\CommandLine\Option)#7 (14) {
    ["short_name"]=>
    string(2) "-b"
    ["long_name"]=>
    string(5) "--foo"
    ["action"]=>
    string(8) "StoreInt"
    ["default"]=>
    int(2)
    ["choices"]=>
    array(3) {
      [0]=>
      int(1)
      [1]=>
      int(2)
      [2]=>
      int(3)
    }
    ["callback"]=>
    NULL
    ["action_params"]=>
    array(0) {
    }
    ["argument_optional"]=>
    bool(false)
    ["add_list_option"]=>
    bool(true)
    ["_action_instance":"PEAR2\Console\CommandLine\Option":private]=>
    NULL
    ["name"]=>
    string(4) "opt2"
    ["help_name"]=>
    string(3) "bar"
    ["description"]=>
    string(19) "description of opt2"
    ["messages"]=>
    array(0) {
    }
  }
  ["list_opt2"]=>
  object(PEAR2\Console\CommandLine\Option)#8 (14) {
    ["short_name"]=>
    NULL
    ["long_name"]=>
    string(11) "--list-opt2"
    ["action"]=>
    string(4) "List"
    ["default"]=>
    NULL
    ["choices"]=>
    array(0) {
    }
    ["callback"]=>
    NULL
    ["action_params"]=>
    array(1) {
      ["list"]=>
      array(3) {
        [0]=>
        int(1)
        [1]=>
        int(2)
        [2]=>
        int(3)
      }
    }
    ["argument_optional"]=>
    bool(false)
    ["add_list_option"]=>
    bool(false)
    ["_action_instance":"PEAR2\Console\CommandLine\Option":private]=>
    NULL
    ["name"]=>
    string(9) "list_opt2"
    ["help_name"]=>
    string(9) "list_opt2"
    ["description"]=>
    string(35) "lists valid choices for option opt2"
    ["messages"]=>
    array(0) {
    }
  }
  ["opt3"]=>
  object(PEAR2\Console\CommandLine\Option)#9 (14) {
    ["short_name"]=>
    NULL
    ["long_name"]=>
    string(5) "--bar"
    ["action"]=>
    string(11) "StoreString"
    ["default"]=>
    NULL
    ["choices"]=>
    array(0) {
    }
    ["callback"]=>
    NULL
    ["action_params"]=>
    array(0) {
    }
    ["argument_optional"]=>
    bool(false)
    ["add_list_option"]=>
    bool(false)
    ["_action_instance":"PEAR2\Console\CommandLine\Option":private]=>
    NULL
    ["name"]=>
    string(4) "opt3"
    ["help_name"]=>
    string(4) "opt3"
    ["description"]=>
    string(19) "description of opt3"
    ["messages"]=>
    array(0) {
    }
  }
}