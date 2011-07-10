--TEST--
Pyrus DER: test parsing of simple DER output with no schema
--FILE--
<?php
require __DIR__ . '/setup.php.inc';

$der->constructed(
        \Pyrus\DER\Sequence::factory()
            ->boolean(true)->boolean(false)
            ->constructed(
                \Pyrus\DER\Set::factory()
                ->bitString('1100')
            )
            ->visibleString('www.asn1.com')
            ->objectIdentifier('1.2.840.113549')
            ->generalizedTime(new DateTime('2009-05-01', new DateTimeZone('America/Chicago')))
            ->integer(460788)
    );

$test->assertEquals(
                    '30' . // sequence identifier
                    '38' . // length
                    '01' . // boolean
                    '01ff' . // length 1, true
                    '01' . // boolean
                    '0100' . // length 1, false
                    '3104' . // set, length 4
                    '03' . // bit stream
                    '0204c0' . // bit stream, length 2, 4 zero-padded bits
                    '1a' . // visibleString
                    '0c' . // length followed by "www.asn1.com"
                    '7777772e61736e312e636f6d' .
                    '0606' . // object identifier, length 6
                    '2a864886f70d' .
                    '180f' . // GeneralizedTime, length 15
                    '32303039303530313035303030305a' .
                    '0203' . // integer, length 3
                    '0707f4', bin2hex($data = $der->serialize()), 'fancy');

$der = new \Pyrus\DER;
$der->parseFromString($data);
$test->assertEquals(
                    '30' . // sequence identifier
                    '38' . // length
                    '01' . // boolean
                    '01ff' . // length 1, true
                    '01' . // boolean
                    '0100' . // length 1, false
                    '3104' . // set, length 4
                    '03' . // bit stream
                    '0204c0' . // bit stream, length 2, 4 zero-padded bits
                    '1a' . // visibleString
                    '0c' . // length followed by "www.asn1.com"
                    '7777772e61736e312e636f6d' .
                    '0606' . // obejct identifier, length 6
                    '2a864886f70d' .
                    '180f' . // GeneralizedTime, length 15
                    '32303039303530313035303030305a' .
                    '0203' . // integer, length 3
                    '0707f4', bin2hex($data = $der->serialize()), 'fancy 2');
?>
===DONE===
--EXPECT--
===DONE===