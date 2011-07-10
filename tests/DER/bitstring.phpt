--TEST--
Pyrus DER: Bit String
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('0304066e5dc0', bin2hex($der->bitString("011011100101110111")->serialize()),
                    "011011100101110111");
$der = new \Pyrus\DER;
$test->assertEquals('0304006e5dc0', bin2hex($der->bitString("011011100101110111000000")->serialize()),
                    "011011100101110111000000");
$der = new \Pyrus\DER;
$test->assertEquals('0304006e5dc0', bin2hex($der->bitString(hexdec('6e5dc0'))->serialize()),
                    '6e5dc0');
?>
===DONE===
--EXPECT--
===DONE===