--TEST--
Sqlite3 registry: test listPackages, multiple packages
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===