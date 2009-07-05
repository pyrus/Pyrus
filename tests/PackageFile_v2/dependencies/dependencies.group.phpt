--TEST--
PackageFile v2: test package.xml dependencies property, dep group
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$reg = new \pear2\Pyrus\PackageFile\v2; // simulate registry package using packagefile
require __DIR__ . '/../../Registry/AllRegistries/package/extended/dependencies.group.template';

?>
===DONE===
--EXPECT--
===DONE===