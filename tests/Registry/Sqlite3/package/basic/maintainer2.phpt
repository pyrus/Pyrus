--TEST--
Sqlite3 PackageFile v2: test package.xml maintainer properties (2)
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../../AllRegistries/package/basic/maintainer2.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===