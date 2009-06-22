--TEST--
Pyrus DER: decodeLength
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals(chr(125), $der->prependTLV('', 125), 'simple');
$test->assertEquals(chr(0x83) . chr(1) . chr(0) . chr(0),
                    $der->prependTLV('', 0x10000), 'complex');
?>
===DONE===
--EXPECT--
===DONE===