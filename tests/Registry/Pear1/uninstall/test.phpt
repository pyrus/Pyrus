--TEST--
Sqlite3 registry: uninstall 1
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
include __DIR__ . '/../../AllRegistries/uninstall/test.template';
?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===