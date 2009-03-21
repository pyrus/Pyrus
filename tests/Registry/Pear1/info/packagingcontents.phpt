--TEST--
Pear1 PackageFile v2: test package.xml packagingcontents property
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../AllRegistries/package/basic/packagingcontents.template';

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===