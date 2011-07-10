--TEST--
\Pyrus\ScriptFrontend\Commands::runScripts
--FILE--
<?php
require __DIR__ . '/setup.runscripts.php.inc';

test_scriptfrontend::$stdin = array(
    '1', // answer for first prompt
    'myfunnyfoo', // answer for value of paramname
    '', // Enter to continue
    '', // Enter to continue, but values needed
    'all', // enter all values, answer for 2nd prompt
    'val1', // value for 2 paramname
    'val2', // value for 2 paramname2
    '', // Enter to continue
    '2', // answer for 3rd prompt
    '3 val2', // value for 3 paramname2
    '', // Enter to continue
    '1', // answer for 3rd prompt #2
    'my thing', // value for 3 paramname
    '', // Enter to continue
    '', // Enter to continue
);
$cli = new test_scriptfrontend();

ob_start();
$cli->run($args = array ('run-scripts', 'testing2'));
$contents = ob_get_contents();
ob_end_clean();

$d = DIRECTORY_SEPARATOR;
$output = 'Using PEAR installation found at ' . TESTDIR . '
Including external post-installation script "' . TESTDIR.$d.'php'.$d.
'foobar" - any errors are in this script
Inclusion succeeded
running post-install script "foobar_postinstall->init()"
init succeeded
testing
this thing
 1. paramname  : 
 2. paramname2 : foo

1-2, \'all\', \'abort\', or Enter to continue: 1
paramname [] : myfunnyfoo
 1. paramname  : myfunnyfoo
 2. paramname2 : foo

1-2, \'all\', \'abort\', or Enter to continue: 
 1. 2 paramname  : 
 2. 2 paramname2 : 

1-2, \'all\', \'abort\', or Enter to continue: 
* ENTER AN ANSWER FOR #1: (2 PARAMNAME)
* ENTER AN ANSWER FOR #2: (2 PARAMNAME2)
 1. 2 paramname  : 
 2. 2 paramname2 : 

1-2, \'all\', \'abort\', or Enter to continue: all
2 paramname [] : val1
2 paramname2 [] : val2
 1. 2 paramname  : val1
 2. 2 paramname2 : val2

1-2, \'all\', \'abort\', or Enter to continue: 
 1. 3 paramname  : 
 2. 3 paramname2 : 

1-2, \'all\', \'abort\', or Enter to continue: 2
3 paramname2 [] : 3 val2
 1. 3 paramname  : 
 2. 3 paramname2 : 3 val2

1-2, \'all\', \'abort\', or Enter to continue: 
* ENTER AN ANSWER FOR #1: (3 PARAMNAME)
 1. 3 paramname  : 
 2. 3 paramname2 : 3 val2

1-2, \'all\', \'abort\', or Enter to continue: 1
3 paramname [] : my thing
 1. 3 paramname  : my thing
 2. 3 paramname2 : 3 val2

1-2, \'all\', \'abort\', or Enter to continue: 
';
$test->assertEquals($output,
                    $contents,
                    'post-install output');

$test->assertEquals(array (
  0 => 
  array (
    'init' => 
    array (
      0 => 'pear2.php.net/testing2',
      1 => NULL,
    ),
  ),
  1 => 
  array (
    'answers' => 
    array (
      'paramname' => 'myfunnyfoo',
      'paramname2' => 'foo',
    ),
    'section' => 'first',
  ),
  2 => 
  array (
    'answers' => 
    array (
      'first::paramname' => 'myfunnyfoo',
      'first::paramname2' => 'foo',
    ),
    'section' => 'second',
  ),
  3 => 
  array (
    'answers' => 
    array (
      'paramname' => 'val1',
      'paramname2' => 'val2',
      'first::paramname' => 'myfunnyfoo',
      'first::paramname2' => 'foo',
    ),
    'section' => 'third',
  ),
  4 => 
  array (
    'answers' => 
    array (
      'paramname' => 'my thing',
      'paramname2' => '3 val2',
      'first::paramname' => 'myfunnyfoo',
      'first::paramname2' => 'foo',
      'third::paramname' => 'val1',
      'third::paramname2' => 'val2',
    ),
    'section' => 'fourth',
  ),
), fake::$captured, 'script info passed in');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===