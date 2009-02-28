--TEST--
PackageFile v2: test package.xml isCompatible()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$pf1 = new PEAR2_Pyrus_PackageFile_v2;
$pf1->name = 'P1';
$pf1->channel = 'pear2.php.net';
$pf1->version['release'] = '1.0.0';

$test->assertEquals('1.0.0', $pf1->version['release'], 'confirm version is what we expect');

$pf2 = new PEAR2_Pyrus_PackageFile_v2;
$pf2->channel = 'pear2.php.net';

$test->assertEquals(false, $pf2->isCompatible($pf1), 'no compatible section');

$pf2->compatible['pear2.php.net/P1']->min('0.9.0')->max('1.2.0');
$pf2->uri = 'http://localhost';

$test->assertEquals(false, $pf2->isCompatible($pf1), 'uri');

$pf2->channel = 'pear2.php.net';

$test->assertEquals(true, $pf2->isCompatible($pf1), 'success');

?>
===DONE===
--EXPECT--
===DONE===