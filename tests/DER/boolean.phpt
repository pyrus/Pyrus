--TEST--
Pyrus DER: Boolean
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('0101ff', bin2hex($der->boolean(true)->serialize()),
                    '0');
$der = new \Pyrus\DER;
$test->assertEquals('010100', bin2hex($der->boolean(false)->serialize()),
                    '127');
?>
===DONE===
--EXPECT--
===DONE===