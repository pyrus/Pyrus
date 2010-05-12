--TEST--
Pyrus DER: OctetString
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$test->assertEquals('040d7465737431407273612e636f6d', bin2hex($der->octetString('test1@rsa.com')->serialize()),
                    'test1@rsa.com');

$der = new \PEAR2\Pyrus\DER;
$test->assertEquals('0481' . dechex(128) . str_repeat('61', 128),
                    bin2hex($der->octetString(str_repeat('a', 128))->serialize()), 'long thing');
?>
===DONE===
--EXPECT--
===DONE===