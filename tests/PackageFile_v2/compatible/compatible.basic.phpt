--TEST--
PackageFile v2: test package.xml compatible property, basic operation
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$reg = new \Pyrus\PackageFile\v2; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/compatible.basic.template';

?>
===DONE===
--EXPECT--
===DONE===