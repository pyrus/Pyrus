--TEST--
PackageFile v2: test package.xml properties, random extra coverage
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$pf = new \Pyrus\PackageFile\v2;
// test array initialization to nada
$pf->fromArray(array('package' => array()));
$test->assertIsa('\Pyrus\PackageFile\v2\Dependencies', $pf->dependencies, 'dependencies');
$test->assertIsa('\Pyrus\PackageFile\v2\Release', $pf->release, 'release');
?>
===DONE===
--EXPECT--
===DONE===