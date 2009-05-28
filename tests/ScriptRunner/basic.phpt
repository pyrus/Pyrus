--TEST--
ScriptRunner: basic post-install script runner test
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$postinstall = $package->files['foobar']->postinstallscript->add();

$postinstall->paramgroup['first']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['first']->param['paramname2']->type('string')->prompt('paramname2');
$postinstall->paramgroup['first']->instructions = "testing\nthis thing";

$postinstall->paramgroup['second']->save();

$postinstall->paramgroup['third']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['third']->param['paramname2']->type('string')->prompt('paramname');

$postinstall->paramgroup['fourth']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['fourth']->param['paramname2']->type('string')->prompt('paramname');

PEAR2_Pyrus_Config::current()->registry->package[] = $package;

$frontend = new fakefrontend;

$frontend->addPrompts(array('paramname' => 'first thingy', 'paramname2' => 'second thingy'));
$frontend->addPrompts(array('paramname' => 'first thingy 2', 'paramname2' => 'second thingy 2'));
$frontend->addPrompts(array('paramname' => 'first thingy 3', 'paramname2' => 'second thingy 3'));
$frontend->addPrompts(array('paramname' => 'first thingy 4', 'paramname2' => 'second thingy 4'));

$runner = new PEAR2_Pyrus_ScriptRunner($frontend);
$runner->run($package);

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
      'paramname' => 'first thingy 2',
      'paramname2' => 'second thingy 2',
    ),
    'section' => 'first',
  ),
  2 => 
  array (
    'answers' => 
    array (
      'first::paramname' => 'first thingy 2',
      'first::paramname2' => 'second thingy 2',
    ),
    'section' => 'second',
  ),
  3 => 
  array (
    'answers' => 
    array (
      'paramname' => 'first thingy 3',
      'paramname2' => 'second thingy 3',
      'first::paramname' => 'first thingy 2',
      'first::paramname2' => 'second thingy 2',
    ),
    'section' => 'third',
  ),
  4 => 
  array (
    'answers' => 
    array (
      'paramname' => 'first thingy 4',
      'paramname2' => 'second thingy 4',
      'first::paramname' => 'first thingy 2',
      'first::paramname2' => 'second thingy 2',
      'third::paramname' => 'first thingy 3',
      'third::paramname2' => 'second thingy 3',
    ),
    'section' => 'fourth',
  ),
), fake::$captured, 'script info passed in');
$test->assertEquals(array (
  0 => "testing\nthis thing",
), fakefrontend::$displayed, 'stuff displayed');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../clean.php.inc';
?>
--EXPECT--
===DONE===