--TEST--
PackageFile v2: test package.xml usesrole property (2)
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new \pear2\Pyrus\PackageFile\v2;
require __DIR__ . '/../../Registry/AllRegistries/package/extended/usesrole.template';

?>
===DONE===
--EXPECT--
===DONE===