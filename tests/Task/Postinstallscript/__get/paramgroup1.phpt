--TEST--
PEAR2_Pyrus_Task_Postinstallscript: accessing paramgroups
--FILE--
<?php
include __DIR__ . '/setup.php.inc';
$postinstall = $package->files['foobar']->postinstallscript->add();
$test->assertIsa('PEAR2_Pyrus_Task_Postinstallscript', $postinstall, 'retrieved postinstallscript');

$test->assertIsa('PEAR2_Pyrus_Task_Postinstallscript_Paramgroup', $postinstall->paramgroup, 'retrieved paramgroup');
$test->assertEquals(0, count($postinstall->paramgroup), 'number of params');

$test->assertIsa('PEAR2_Pyrus_Task_Postinstallscript_Paramgroup', $postinstall->paramgroup['foo'],
                 'retrieved named paramgroup');

$test->assertIsa('PEAR2_Pyrus_Task_Postinstallscript_Paramgroup_Param', $postinstall->paramgroup['foo']->param,
                 'retrieved param');

$test->assertFalse(isset($postinstall->paramgroup['foo']), 'isset(foo)');

$test->assertEquals('foo', $postinstall->paramgroup['foo']->id, '__get id');
$test->assertTrue(isset($postinstall->paramgroup['foo']->id), 'isset(foo->id)');
$test->assertFalse(isset($postinstall->paramgroup['foo']->instructions), 'isset(foo->instructions)');
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===