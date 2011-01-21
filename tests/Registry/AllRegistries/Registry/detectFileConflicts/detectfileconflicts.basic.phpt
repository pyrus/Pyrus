--TEST--
Registry: test detectFileConflicts
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';
$dir = TESTDIR . DIRECTORY_SEPARATOR;
require __DIR__ . '/../../detectfileconflicts/basic.template';

?>
===DONE===
--CLEAN--
<?php
include __DIR__ . '/../../../../clean.php.inc';
?>
--EXPECT--
===DONE===