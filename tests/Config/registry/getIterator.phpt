--TEST--
Confirm: GH-23
--FILE--
<?php
require __DIR__ . '/../setup.php.inc';

$c = getTestConfig();

foreach ($c->registry as $r) {
}
?>
===DONE===
--CLEAN--
<?php
$dir = __DIR__ . '/testit';
include __DIR__ . '/../../clean.php.inc';
?>
--EXPECT--
===DONE===
