--TEST--
PackageFile v2: test package.xml usestask property
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$reg = new \pear2\Pyrus\PackageFile\v2;
require __DIR__ . '/../../Registry/AllRegistries/package/extended/usestask.template';

?>
===DONE===
--EXPECT--
===DONE===