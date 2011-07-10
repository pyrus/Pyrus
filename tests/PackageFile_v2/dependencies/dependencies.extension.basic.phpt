--TEST--
PackageFile v2: test package.xml dependencies property, basic extension dep
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$reg = new \Pyrus\PackageFile\v2; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/dependencies.extension.basic.template';

?>
===DONE===
--EXPECT--
===DONE===