--TEST--
PackageFile v2: test package.xml compatible property, errors
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$package = new PEAR2_Pyrus_PackageFile_v2; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/compatible.errors.template';

?>
===DONE===
--EXPECT--
===DONE===