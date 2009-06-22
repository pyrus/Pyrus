--TEST--
Pyrus DER: parse an actual OCSP request
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
PEAR2_Pyrus_DER_Schema::addType('AnotherName',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('AnotherName')
        ->objectIdentifier('type-id')
        ->any('value', 0));

PEAR2_Pyrus_DER_Schema::addType('GeneralName',
    PEAR2_Pyrus_DER_Schema::factory()
    ->choice('GeneralName')
        ->option('otherName', 'AnotherName')
        ->option('rfc822Name', 'IA5String')
        ->option('dNSName', 'IA5String')
        ->option('x400Address', 'AnotherName') // ORaddress, I'm lazy
        ->option('dNSName', 'IA5String'));

PEAR2_Pyrus_DER_Schema::addType('AlgorithmIdentifier',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('AlgorithmIdentifier')
        ->objectIdentifier('algorithm')
        ->any('parameters'));

PEAR2_Pyrus_DER_Schema::addType('CertID',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('CertID')
        ->algorithmIdentifier('hashAlgorithm')
        ->octetString('issuerNameHash')
        ->octetString('issuerKeyHash')
        ->integer('serialNumber'));

PEAR2_Pyrus_DER_Schema::addType('Extensions',
    $extensions = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('Extensions')
        ->sequence('Inner')
            ->sequence('Extension')->setMultiple()
                ->objectIdentifier('extnID')
                ->boolean('critical')
                ->octetString('extnValue')
            ->end()
        ->end());
$extensions->Inner->Extension->critical->setOptional();

PEAR2_Pyrus_DER_Schema::addType('Request',
    $request = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('Request')
        ->certID('reqCert')
        ->extensions('singleRequestExtensions', 0));
$request->singleRequestExtensions->setOptional();

PEAR2_Pyrus_DER_Schema::addType('TBSRequest',
    $tbs = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('TBSRequest')
        ->integer('version', 0)
        ->generalName('requestorName', 1)
        ->sequence('test')
            ->request('requestList')
        ->end()
        ->extensions('requestExtensions', 2)
    );
$tbs->version->setOptional();
$tbs->requestorName->setOptional();
$tbs->test->requestList->setMultiple();
$tbs->requestExtensions->setOptional();

$schema = new PEAR2_Pyrus_DER_Schema;
$schema
        ->sequence('OCSPRequest')
            ->TBSRequest('tbsRequest');
        //->Signature('optionalSignature', 0);
$der->setSchema($schema);

try {
    $der->parseFromString(file_get_contents(__DIR__ . '/ocsp/request.ocsp'));
    $test->assertEquals('
 OCSPRequest [sequence]: 
  tbsRequest [sequence]: 
   test [sequence]: 
    requestList [sequence]: 
     reqCert [sequence]: 
      hashAlgorithm [sequence]: 
       algorithm [objectIdentifier] (1.3.14.3.2.26 [SHA-1 hash algorithm])
       parameters [null] ()
      end hashAlgorithm

      issuerNameHash [octetString] (8ba4c9cb172919453ebb8e730991b925f2832265)
      issuerKeyHash [octetString] (16b5321bd4c7f3e0e68ef3bdd2b03aeeb23918d1)
      serialNumber [integer] (460788)
     end reqCert
    end requestList
   end test

   requestExtensions [sequence]: 
    Inner [sequence]: 
     Extension [sequence]: 
      extnID [objectIdentifier] (1.3.6.1.5.5.7.48.1.2 [OCSP nonce])
      extnValue [octetString] (0410139ca1bc5dbc7cb422d547b6d730ca87)
     end Extension
    end Inner
   end requestExtensions
  end tbsRequest
 end OCSPRequest
end 
', (string) $der, 'after parsing');
} catch (Exception $e) {
    echo $e->getMessage(), "\n", $der;
}
?>
===DONE===
--EXPECT--
===DONE===