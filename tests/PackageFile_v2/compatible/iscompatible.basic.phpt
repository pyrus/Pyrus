--TEST--
PackageFile v2: test package.xml isCompatible()
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$pf1 = new \PEAR2\Pyrus\PackageFile\v2;
$pf1->name = 'P1';
$pf1->channel = 'pear2.php.net';
$pf1->version['release'] = '1.0.0';

$test->assertEquals('1.0.0', $pf1->version['release'], 'confirm version is what we expect');

$pf2 = new \PEAR2\Pyrus\PackageFile\v2;
$pf2->channel = 'pear2.php.net';

$test->assertEquals(false, $pf2->isCompatible($pf1), 'no compatible section');

$pf2->compatible['pear2.php.net/P1']->min('0.9.0')->max('1.2.0');
$pf2->uri = 'http://localhost';

$test->assertEquals(false, $pf2->isCompatible($pf1), 'uri');

$pf2->channel = 'pear2.php.net';

$test->assertEquals(true, $pf2->isCompatible($pf1), 'success');

$pf2->compatible['pear2.php.net/P1']->exclude('1.0.0');

$test->assertEquals(false, $pf2->isCompatible($pf1), 'fail exclude');

$pf2->compatible['pear2.php.net/P1']->exclude(null);
$pf2->compatible['pear2.php.net/P1']->min('1.1.0');

$test->assertEquals(false, $pf2->isCompatible($pf1), 'fail min');

$pf2->compatible['pear2.php.net/P1']->min('1.0.0');

$test->assertEquals(true, $pf2->isCompatible($pf1), 'success min bounds');

$pf2->compatible['pear2.php.net/P1']->min('0.9.0');
$pf2->compatible['pear2.php.net/P1']->max('0.9.9');

$test->assertEquals(false, $pf2->isCompatible($pf1), 'fail max');
$pf2->compatible['pear2.php.net/P1']->max('1.0.0');

$test->assertEquals(true, $pf2->isCompatible($pf1), 'success max bounds');

$pf1->name = 'notfound';

$test->assertEquals(false, $pf2->isCompatible($pf1), 'fail not found');

?>
===DONE===
--EXPECT--
===DONE===