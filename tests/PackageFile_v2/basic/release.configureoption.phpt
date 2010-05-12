--TEST--
PackageFile v2: test package.xml release configureoption property
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new \PEAR2\Pyrus\PackageFile\v2;
require __DIR__ . '/../../Registry/AllRegistries/package/extended/release.configureoption.template';
?>
===DONE===
--EXPECT--
===DONE===