--TEST--
PackageFile v2: test package.xml dependsOn(), channel-based packages
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$pf1 = new \Pyrus\PackageFile\v2;
$pf1->name = 'P1';
$pf1->channel = 'pear2.php.net';

$pf2 = new \Pyrus\PackageFile\v2;
$pf2->name = 'P2';
$pf2->channel = 'pear.example.com';

$test->assertEquals(false, $pf1->dependsOn($pf2), 'no relation');
$pf1->dependencies['group']->group1->package['pear.example.com/P2']->save();
$test->assertEquals(true, $pf1->dependsOn($pf2), 'group relation');

unset($pf1->dependencies['group']);

$test->assertEquals(false, $pf1->dependsOn($pf2), 'no relation');
$pf1->dependencies['optional']->package['pear.example.com/P2']->save();
$test->assertEquals(true, $pf1->dependsOn($pf2), 'optional relation');

unset($pf1->dependencies['optional']);

$test->assertEquals(false, $pf1->dependsOn($pf2), 'no relation');
$pf1->dependencies['required']->package['pear.example.com/P2']->save();
$test->assertEquals(true, $pf1->dependsOn($pf2), 'required relation');

?>
===DONE===
--EXPECT--
===DONE===