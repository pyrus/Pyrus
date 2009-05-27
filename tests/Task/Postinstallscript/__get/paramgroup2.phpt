--TEST--
PEAR2_Pyrus_Task_Postinstallscript: accessing paramgroups 2
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$postinstall = $package->files['foobar']->postinstallscript;

$test->assertFalse(isset($postinstall[0]->paramgroup['foo']), 'isset(foo)');
$test->assertFalse(isset($postinstall[0]->paramgroup['foo']->instructions), 'isset(foo->instructions)');
$test->assertEquals(null, $postinstall[0]->paramgroup['foo']->instructions, 'instructions value');

$postinstall[0]->paramgroup['foo']->instructions = 'hi';

$test->assertTrue(isset($postinstall[0]->paramgroup['foo']), 'isset(foo) after');
$test->assertTrue(isset($postinstall[0]->paramgroup['foo']->instructions), 'isset(foo->instructions) after');
$test->assertEquals('hi', $postinstall[0]->paramgroup['foo']->instructions, 'instructions value after');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===