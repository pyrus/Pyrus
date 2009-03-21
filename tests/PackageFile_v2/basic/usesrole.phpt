--TEST--
PackageFile v2: test package.xml usesrole property
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new PEAR2_Pyrus_PackageFile_v2;
require __DIR__ . '/../../Registry/AllRegistries/package/extended/usesrole.template';

?>
===DONE===
--EXPECT--
===DONE===