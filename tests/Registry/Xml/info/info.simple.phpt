--TEST--
Xml registry: test basic info properties
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
require __DIR__ . '/../../AllRegistries/info/simple.template';

?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===