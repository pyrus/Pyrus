--TEST--
\PEAR2\Pyrus\Task\Postinstallscript: setting paramgroup condition
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$postinstall = $package->files['foobar']->postinstallscript->add();

$test->assertFalse(isset($postinstall->paramgroup['foo']->conditiontype), 'isset(foo->conditiontype)');
$test->assertFalse(isset($postinstall->paramgroup['foo']->name), 'isset(foo->name)');
$test->assertFalse(isset($postinstall->paramgroup['foo']->value), 'isset(foo->value)');
$test->assertEquals(null, $postinstall->paramgroup['foo']->conditiontype, 'conditiontype value');
$test->assertEquals(null, $postinstall->paramgroup['foo']->name, 'conditiontype value');
$test->assertEquals(null, $postinstall->paramgroup['foo']->value, 'conditiontype value');

$postinstall->paramgroup['previous']->param['first']->prompt = 'my prompt';
$postinstall->paramgroup['foo']->condition($postinstall->paramgroup['previous']->param['first'],
                                                 '!=', '5');

$test->assertTrue(isset($postinstall->paramgroup['foo']->conditiontype), 'isset(foo->conditiontype) after');
$test->assertTrue(isset($postinstall->paramgroup['foo']->name), 'isset(foo->name) after');
$test->assertTrue(isset($postinstall->paramgroup['foo']->value), 'isset(foo->value) after');
$test->assertEquals('!=', $postinstall->paramgroup['foo']->conditiontype, 'conditiontype value after');
$test->assertEquals('previous::first', $postinstall->paramgroup['foo']->name, 'conditiontype value after');
$test->assertEquals('5', $postinstall->paramgroup['foo']->value, 'conditiontype value after');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===