--TEST--
Pyrus DER: decodeLength
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals(array(1, 2), $der->decodeLength(chr(2), 0), 'simple');
$test->assertEquals(array(4, 0x10000), $der->decodeLength(chr(0x83) . chr(0x1) . chr(0) . chr(0), 0), 'complex');
?>
===DONE===
--EXPECT--
===DONE===