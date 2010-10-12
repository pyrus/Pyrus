--TEST--
\PEAR2\Pyrus\Task\Postinstallscript: accessing params
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$postinstall = $package->files['foobar']->postinstallscript->add();

$test->assertFalse(isset($postinstall->paramgroup['foo']->param), 'isset(foo->param)');
$test->assertEquals(0, count($postinstall->paramgroup['foo']->param), 'count(foo->param)');
$test->assertFalse(isset($postinstall->paramgroup['foo']->param['first']), 'isset(foo->param[first])');
$test->assertFalse(isset($postinstall->paramgroup['foo']->param['first']->prompt), 'isset(foo->param[first])');
$test->assertEquals('first', $postinstall->paramgroup['foo']->param['first']->name, 'name value');
$test->assertEquals(null, $postinstall->paramgroup['foo']->param['first']->prompt, 'prompt value');

$postinstall->paramgroup['foo']->param['first']->prompt = 'my prompt';

$test->assertTrue(isset($postinstall->paramgroup['foo']->param), 'isset(foo->param) after');
$test->assertEquals(1, count($postinstall->paramgroup['foo']->param), 'count(foo->param) after');
$test->assertTrue(isset($postinstall->paramgroup['foo']->param['first']), 'isset(foo->param[first]) after');
$test->assertTrue(isset($postinstall->paramgroup['foo']->param['first']->prompt), 'isset(foo->param[first]) after');
$test->assertEquals('first', $postinstall->paramgroup['foo']->param['first']->name, 'name value after');
$test->assertEquals('my prompt', $postinstall->paramgroup['foo']->param['first']->prompt, 'prompt value after');
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===