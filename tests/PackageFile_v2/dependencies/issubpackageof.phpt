--TEST--
PackageFile v2: test package.xml isSubpackageOf()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$pf1 = new PEAR2_Pyrus_PackageFile_v2;
$pf1->name = 'P1';
$pf1->uri = 'http://localhost';

$pf2 = new PEAR2_Pyrus_PackageFile_v2;
$pf2->name = 'P2';
$pf2->uri = 'http://example.com';

$test->assertEquals(false, $pf2->isSubpackageOf($pf1), 'no relation');

$pf1->dependencies['group']->group1->subpackage['__uri/P2']->uri('http://example.com');
$test->assertEquals(true, $pf2->isSubpackageOf($pf1), 'group relation');

?>
===DONE===
--EXPECT--
===DONE===