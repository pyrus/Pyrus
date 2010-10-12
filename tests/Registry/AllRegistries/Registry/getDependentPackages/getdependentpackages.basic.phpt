--TEST--
Registry: test getDependentPackages
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../getdependentpackages/basic.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===