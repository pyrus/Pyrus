--TEST--
PackageFile v2: test package.xml release configureoption property errors
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new PEAR2_Pyrus_PackageFile_v2;
require __DIR__ . '/../../Registry/AllRegistries/info/release.configureoption.errors.template';
?>
===DONE===
--EXPECT--
===DONE===