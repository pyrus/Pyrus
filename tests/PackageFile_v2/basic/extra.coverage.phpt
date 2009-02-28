--TEST--
PackageFile v2: test package.xml properties, random extra coverage
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$pf = new PEAR2_Pyrus_PackageFile_v2;
// test array initialization to nada
$pf->fromArray(array('package' => array()));
$test->assertIsa('PEAR2_Pyrus_PackageFile_v2_Dependencies', $pf->dependencies, 'dependencies');
$test->assertIsa('PEAR2_Pyrus_PackageFile_v2_Release', $pf->release, 'release');
?>
===DONE===
--EXPECT--
===DONE===