--TEST--
Sqlite3 PackageFile v2: test package.xml allmaintainers property
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../AllRegistries/package/basic/allmaintainers.template';

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===