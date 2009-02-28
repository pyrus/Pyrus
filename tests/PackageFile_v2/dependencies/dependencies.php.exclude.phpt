--TEST--
PackageFile v2: test package.xml dependencies property, multiple exclude in php dep
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$reg = new PEAR2_Pyrus_PackageFile_v2; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/info/dependencies.php.exclude.template';

?>
===DONE===
--EXPECT--
===DONE===