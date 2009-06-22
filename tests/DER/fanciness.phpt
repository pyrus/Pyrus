--TEST--
Pyrus DER: test chained construction of complex DER output
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
$schema = new PEAR2_Pyrus_DER_Schema;
$schema
    ->sequence('first')
        ->boolean('fbool', 0)
        ->boolean('bool2', 1)
        ->set('inner', 2)
            ->bitstring('bstring', 0)
        ->end()
        ->visibleString('vstring', 3);
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
                    '81' . // context tag value 1 (boolean)
                    '0100' . // length 1, false
                    'a204' . // set, length 4, context tag value 2
                    '80' . // context tag value 0
                    '0204c0' . // bit stream, length 2, 4 zero-padded bits
                    '83' . // context tag value 3 (visibleString)
                    '0c' . // length followed by "www.asn1.com"
                    '7777772e61736e312e636f6d', bin2hex($der->serialize()), 'fancy');
?>
===DONE===
--EXPECT--
===DONE===