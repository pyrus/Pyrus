--TEST--
PackageFile v2: test package.xml dependencies property, setting extension dep
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$reg = new PEAR2_Pyrus_PackageFile_v2; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/dependencies.extension.setting.template';

?>
===DONE===
--EXPECT--
===DONE===