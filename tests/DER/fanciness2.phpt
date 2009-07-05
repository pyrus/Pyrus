--TEST--
Pyrus DER: test chained construction of complex DER output 2
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
        ->visibleString('vstring');
$der->setSchema($schema);
$first = $der->first;
$first->fbool = true;
$first->bool2 = false;
$first->inner->bstring = '1100';
$first->vstring = 'www.asn1.com';
$test->assertEquals(
                    '30' . // sequence identifier
                    '1a' . // length
                    '80' . // context tag value 0 (boolean)
                    '01ff' . // length 1, true
                    '01' . // boolean
                    '0100' . // length 1, false
                    '3104' . // set, length 4
                    '03' . // bit stream
                    '0204c0' . // bit stream, length 2, 4 zero-padded bits
                    '1a' . // visibleString
                    '0c' . // length followed by "www.asn1.com"
                    '7777772e61736e312e636f6d', bin2hex($der->serialize()), 'fancy');
?>
===DONE===
--EXPECT--
===DONE===