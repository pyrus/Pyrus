--TEST--
PackageFile v2: test package.xml usestask property (2)
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new \PEAR2\Pyrus\PackageFile\v2;
require __DIR__ . '/../../Registry/AllRegistries/package/extended/usestask2.template';

?>
===DONE===
--EXPECT--
===DONE===