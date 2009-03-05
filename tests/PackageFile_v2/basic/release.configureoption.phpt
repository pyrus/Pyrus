--TEST--
PackageFile v2: test package.xml release configureoption property
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new PEAR2_Pyrus_PackageFile_v2;
require __DIR__ . '/../../Registry/AllRegistries/info/release.configureoption.template';
?>
===DONE===
--EXPECT--
===DONE===