--TEST--
PackageFile v2: test package.xml license property, direct set
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new \Pyrus\PackageFile\v2;
require __DIR__ . '/../../Registry/AllRegistries/package/extended/setlicense.template';

?>
===DONE===
--EXPECT--
===DONE===