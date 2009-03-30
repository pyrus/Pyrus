--TEST--
PackageFile v2: test subpackage.xml dependsOn(), uri-based subpackages
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$pf1 = new PEAR2_Pyrus_PackageFile_v2;
$pf1->name = 'P1';
$pf1->channel = 'pear2.php.net';

$pf2 = new PEAR2_Pyrus_PackageFile_v2;
$pf2->name = 'P2';
$pf2->uri = 'http://pear.example.com';

$test->assertEquals(false, $pf1->dependsOn($pf2), 'no relation');
$pf1->dependencies['group']->group1->subpackage['__uri/P2']->uri('http://pear.example.com');
$test->assertEquals(true, $pf1->dependsOn($pf2), 'group relation');

unset($pf1->dependencies['group']);

$test->assertEquals(false, $pf1->dependsOn($pf2), 'no relation');
$pf1->dependencies['optional']->subpackage['__uri/P2']->uri('http://pear.example.com');
$test->assertEquals(true, $pf1->dependsOn($pf2), 'optional relation');

unset($pf1->dependencies['optional']);

$test->assertEquals(false, $pf1->dependsOn($pf2), 'no relation');
$pf1->dependencies['required']->subpackage['__uri/P2']->uri('http://pear.example.com');
$test->assertEquals(true, $pf1->dependsOn($pf2), 'required relation');

?>
===DONE===
--EXPECT--
===DONE===