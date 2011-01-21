--TEST--
PackageFile v2: test package.xml packagingcontents property
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../Registry/AllRegistries/package/basic/packagingcontents.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===