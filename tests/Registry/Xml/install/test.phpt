--TEST--
Xml registry: install 1
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
include __DIR__ . '/../../AllRegistries/install/test.template';
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===