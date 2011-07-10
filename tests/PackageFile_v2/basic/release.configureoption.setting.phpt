--TEST--
PackageFile v2: test package.xml setting release configureoption property
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new \Pyrus\PackageFile\v2;
require __DIR__ . '/../../Registry/AllRegistries/package/extended/release.configureoption.setting.template';
?>
===DONE===
--EXPECT--
===DONE===