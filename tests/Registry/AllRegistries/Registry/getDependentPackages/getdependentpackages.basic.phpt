--TEST--
Registry: test getDependentPackages
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../getdependentpackages/basic.template';

?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===