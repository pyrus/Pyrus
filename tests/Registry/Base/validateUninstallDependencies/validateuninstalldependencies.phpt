--TEST--
Registry base: test validateUninstallDependenices()
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;

$p1 = new \PEAR2\Pyrus\PackageFile\v2;
$p1->name = 'foo';
$p1->channel = 'pear2.php.net';
$p1->version['release'] = '1.2.3';
$p1->files['foo'] = array('role' => 'php');
$p1->notes = 'hi';
$p1->summary = 'hi';
$p1->description = 'hi';
$p1->dependencies['required']->package['pear2.php.net/test']->min('1.2.4');

$reg->install($p1);

$p2 = new \PEAR2\Pyrus\PackageFile\v2;
$p2->name = 'foo2';
$p2->channel = 'pear2.php.net';
$p2->version['release'] = '1.2.3';
$p2->files['foo2'] = array('role' => 'php');
$p2->notes = 'hi';
$p2->summary = 'hi';
$p2->description = 'hi';
$p2->dependencies['required']->subpackage['pear2.php.net/test']->min('1.2.4');

$reg->install($p2);

$p3 = new \PEAR2\Pyrus\PackageFile\v2;
$p3->name = 'test';
$p3->channel = 'pear2.php.net';
$p3->version['release'] = '1.2.3';
$p3->files['test'] = array('role' => 'php');
$p3->notes = 'hi';
$p3->summary = 'hi';
$p3->description = 'hi';

$reg->install($p3);

$package = $reg->package['pear2.php.net/test'];

$test->assertEquals(true, $package->validateUninstallDependencies(array($package), $errs), 'test');
$test->assertEquals(0, count($errs), 'test error count');

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===