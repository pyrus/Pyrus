--TEST--
\pear2\Pyrus\Task\Postinstallscript: paramgroup matchesConditionType()
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$postinstall = $package->files['foobar']->postinstallscript->add();

$postinstall->paramgroup['first']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['first']->param['paramname2']->type('string')->prompt('paramname2');

$postinstall->paramgroup['second']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['second']->param['paramname2']->type('string')->prompt('paramname2');

$postinstall->paramgroup['third']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['third']->param['paramname2']->type('string')->prompt('paramname');

$postinstall->paramgroup['fourth']->param['paramname']->type('string')->prompt('paramname');
$postinstall->paramgroup['fourth']->param['paramname2']->type('string')->prompt('paramname');

$postinstall->paramgroup['third']->condition($postinstall->paramgroup['first']->param['paramname'],
                                             '=', 'hi');

$test->assertTrue($postinstall->paramgroup['third']->matchesConditionType(
                          array('first::paramname' => 'hi')),
                    'first test');

$test->assertFalse($postinstall->paramgroup['third']->matchesConditionType(
                          array('first::paramname' => 'bye')),
                    'first test failure');

$postinstall->paramgroup['third']->condition($postinstall->paramgroup['first']->param['paramname'],
                                             '!=', 'hi');

$test->assertTrue($postinstall->paramgroup['third']->matchesConditionType(
                          array('first::paramname' => 'bye')),
                    'second test');

$test->assertFalse($postinstall->paramgroup['third']->matchesConditionType(
                          array('first::paramname' => 'hi')),
                    'second test failure');

$postinstall->paramgroup['third']->condition($postinstall->paramgroup['first']->param['paramname'],
                                             'preg_match', 'h[iI]');

$test->assertTrue($postinstall->paramgroup['third']->matchesConditionType(
                          array('first::paramname' => 'hi')),
                    'third test 1');

$test->assertTrue($postinstall->paramgroup['third']->matchesConditionType(
                          array('first::paramname' => 'hI')),
                    'third test 2');

$test->assertFalse($postinstall->paramgroup['third']->matchesConditionType(
                          array('first::paramname' => 'HI')),
                    'third test failure');

$test->assertTrue($postinstall->paramgroup['second']->matchesConditionType(
                          array('first::paramname' => 'hi')),
                    'test no condition set');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===