--TEST--
PackageFile v2: test package.xml usestask property (2)
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new PEAR2_Pyrus_PackageFile_v2;
require __DIR__ . '/../../Registry/AllRegistries/info/usestask2.template';

?>
===DONE===
--EXPECT--
===DONE===