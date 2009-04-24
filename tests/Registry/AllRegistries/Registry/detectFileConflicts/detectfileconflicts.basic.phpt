--TEST--
Registry: test detectFileConflicts
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$dir = __DIR__ . DIRECTORY_SEPARATOR . 'testit' . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../detectfileconflicts/basic.template';

?>
===DONE===
--CLEAN--
<?php
$dir = dirname(__DIR__) . '/testit';
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===