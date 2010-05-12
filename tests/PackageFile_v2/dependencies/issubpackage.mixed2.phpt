--TEST--
PackageFile v2: test package.xml isSubpackage(), uri-based mixed with channel (2)
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$pf1 = new \PEAR2\Pyrus\PackageFile\v2;
$pf1->name = 'P1';
$pf1->uri = 'http://localhost';

$pf2 = new \PEAR2\Pyrus\PackageFile\v2;
$pf2->name = 'P2';
$pf2->channel = 'pear2.php.net';

$test->assertEquals(false, $pf1->isSubpackage($pf2), 'no relation');

$pf1->dependencies['group']->group1->subpackage['pear2.php.net/P2']->save();
$test->assertEquals(true, $pf1->isSubpackage($pf2), 'group relation');

unset($pf1->dependencies['group']);

$pf1->dependencies['optional']->subpackage['pear2.php.net/P2']->save();
$test->assertEquals(true, $pf1->isSubpackage($pf2), 'optional relation');

unset($pf1->dependencies['optional']);

$pf1->dependencies['required']->subpackage['pear2.php.net/P2']->save();
$test->assertEquals(true, $pf1->isSubpackage($pf2), 'required relation');

?>
===DONE===
--EXPECT--
===DONE===