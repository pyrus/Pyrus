--TEST--
Pyrus DER: test parsing of complex DER output
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$schema = new \pear2\Pyrus\DER\Schema;
$schema
    ->sequence('first')
        ->boolean('fbool', 0)
        ->boolean('bool2')
        ->set('inner')
            ->bitstring('bstring')
        ->end()
        ->visibleString('vstring')
        ->objectIdentifier('oid')
        ->UTCTime('utctime')
        ->integer('int');
$der->setSchema($schema);
$first = $der->first;
$first->fbool = true;
$first->bool2 = false;
$first->inner->bstring = '1100';
$first->vstring = 'www.asn1.com';
$first->oid = '1.2.840.113549';
$first->utctime = new DateTime('2009-05-01', new DateTimeZone('America/Chicago'));
$first->int = 460788;

$test->assertEquals(
                    '30' . // sequence identifier
                    '36' . // length
                    '80' . // context tag value 0 (boolean)
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
                    '170d' . // UTCTime, length 13
                    '3039303530313035303030305a' .
                    '0203' . // integer, length 3
                    '0707f4', bin2hex($data = $der->serialize()), 'fancy');

$der = new \pear2\Pyrus\DER;
$der->setSchema($schema);
$der->parseFromString($data);
$test->assertEquals(
                    '30' . // sequence identifier
                    '36' . // length
                    '80' . // context tag value 0 (boolean)
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
                    '170d' . // UTCTime, length 13
                    '3039303530313035303030305a' .
                    '0203' . // integer, length 3
                    '0707f4', bin2hex($data = $der->serialize()), 'fancy 2');
?>
===DONE===
--EXPECT--
===DONE===