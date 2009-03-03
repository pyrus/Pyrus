--TEST--
PackageFile v2: test package.xml license property, direct set
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new PEAR2_Pyrus_PackageFile_v2;
require __DIR__ . '/../../Registry/AllRegistries/info/setlicense.template';

?>
===DONE===
--EXPECT--
===DONE===