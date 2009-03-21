--TEST--
Xml PackageFile v2: test basic package.xml properties
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../AllRegistries/package/basic/license.template';

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===