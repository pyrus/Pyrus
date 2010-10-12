--TEST--
Pear1 PackageFile v2: test package.xml release properties (2)
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../../AllRegistries/package/basic/release2.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===