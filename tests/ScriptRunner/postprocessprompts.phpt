--TEST--
ScriptRunner: post-install script runner test, with post-processing of prompts
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
file_put_contents(__DIR__ . '/testit/php/foobar', '<?php
class foobar_postinstall extends fake2 {}');

class fake2 extends fake
{
    function postProcessPrompts2(array $prompts, $section)
    {
        fakefrontend::$displayed[] = array('postprocessprompts' => $prompts, 'section' => $section);
        if ($section === 'fourth') {
            foreach ($prompts as $i => $prompt) {
                $prompts[$i]['default'] = 'booya';
                $prompts[$i]['prompt'] = 'I am teh fail, are you?';
            }
        }
        return $prompts;
    }

    function run2(array $answers, $section)
    {
        // for coverage, also test skipParamgroup
        \pear2\Pyrus\ScriptRunner::skipParamgroup('third');
        return parent::run2($answers, $section);
    }
}

$postinstall = $package->files['foobar']->postinstallscript->add();

$postinstall->paramgroup['first']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['first']->param['paramname2']->type('string')->prompt('paramname2');
$postinstall->paramgroup['first']->instructions = "testing\nthis thing";

$postinstall->paramgroup['second']->save();

$postinstall->paramgroup['third']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['third']->param['paramname2']->type('string')->prompt('paramname');

$postinstall->paramgroup['fourth']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['fourth']->param['paramname2']->type('string')->prompt('paramname');

\pear2\Pyrus\Config::current()->registry->package[] = $package;

$frontend = new fakefrontend;

$frontend->addPrompts(array('paramname' => 'first thingy', 'paramname2' => 'second thingy'));
$frontend->addPrompts(array('paramname' => 'first thingy 2', 'paramname2' => 'second thingy 2'));
$frontend->addPrompts(array('paramname' => 'first thingy 4', 'paramname2' => 'second thingy 4'));

$runner = new \pear2\Pyrus\ScriptRunner($frontend);
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
      'paramname' => 'first thingy 4',
      'paramname2' => 'second thingy 4',
      'first::paramname' => 'first thingy 2',
      'first::paramname2' => 'second thingy 2',
    ),
    'section' => 'fourth',
  ),
), fake::$captured, 'script info passed in');
$test->assertEquals(array (
  0 => 'testing
this thing',
  1 => 
  array (
    'postprocessprompts' => 
    array (
      0 => 
      array (
        'name' => 'paramname',
        'prompt' => 'paramname',
        'type' => 'string',
      ),
      1 => 
      array (
        'name' => 'paramname2',
        'prompt' => 'paramname2',
        'type' => 'string',
      ),
    ),
    'section' => 'first',
  ),
  2 => 
  array (
    0 => 
    array (
      'name' => 'paramname',
      'prompt' => 'paramname',
      'type' => 'string',
    ),
    1 => 
    array (
      'name' => 'paramname2',
      'prompt' => 'paramname2',
      'type' => 'string',
    ),
  ),
  3 => 
  array (
    'postprocessprompts' => 
    array (
      0 => 
      array (
        'name' => 'paramname',
        'prompt' => 'paramname',
        'type' => 'string',
      ),
      1 => 
      array (
        'name' => 'paramname2',
        'prompt' => 'paramname',
        'type' => 'string',
      ),
    ),
    'section' => 'fourth',
  ),
  4 => 
  array (
    0 => 
    array (
      'name' => 'paramname',
      'prompt' => 'I am teh fail, are you?',
      'type' => 'string',
      'default' => 'booya',
    ),
    1 => 
    array (
      'name' => 'paramname2',
      'prompt' => 'I am teh fail, are you?',
      'type' => 'string',
      'default' => 'booya',
    ),
  ),
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