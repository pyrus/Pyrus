--TEST--
Xml registry: test listPackages, multiple packages, multiple channels
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../AllRegistries/listpackages/multiple.channels.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../clean.php.inc';
?>
--EXPECT--
===DONE===