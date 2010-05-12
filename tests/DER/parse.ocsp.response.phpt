--TEST--
Pyrus DER: parse an actual OCSP request
--FILE--
<?php
require __DIR__ . '/setup.php.inc';
\PEAR2\Pyrus\DER\Schema::addType('AnotherName',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('AnotherName')
        ->objectIdentifier('type-id')
        ->any('value', 0));

\PEAR2\Pyrus\DER\Schema::addType('GeneralName',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->choice('GeneralName')
        ->option('otherName', 'AnotherName')
        ->option('rfc822Name', 'IA5String')
        ->option('dNSName', 'IA5String')
        ->option('x400Address', 'AnotherName') // ORaddress, I'm lazy
        ->option('dNSName', 'IA5String'));

\PEAR2\Pyrus\DER\Schema::addType('AlgorithmIdentifier',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('AlgorithmIdentifier')
        ->objectIdentifier('algorithm')
        ->any('parameters'));

\PEAR2\Pyrus\DER\Schema::addType('CertID',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('CertID')
        ->algorithmIdentifier('hashAlgorithm')
        ->octetString('issuerNameHash')
        ->octetString('issuerKeyHash')
        ->integer('serialNumber'));

\PEAR2\Pyrus\DER\Schema::addType('Extensions',
    $extensions = \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('Extensions')
        ->sequence('Inner')
            ->sequence('Extension')->setMultiple()
                ->objectIdentifier('extnID')
                ->boolean('critical')
                ->octetString('extnValue')
            ->end()
        ->end());
$extensions->Inner->Extension->critical->setOptional();

\PEAR2\Pyrus\DER\Schema::addType('RevokedInfo',
    $revoked = \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('RevokedInfo')
        ->generalizedTime('revocationTime')
        ->enumerated('revocationReason', 0)
    );
$revoked->revocationReason->setOptional();

\PEAR2\Pyrus\DER\Schema::addType('CertStatus',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->choice('CertStatus')
        ->option('good', 'null')
        ->option('revoked', 'RevokedInfo')
        ->option('unknown', 'any')
    );

\PEAR2\Pyrus\DER\Schema::addType('SingleResponse',
    $single = \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('SingleResponse')
        ->CertID('cert')
        ->CertStatus('status')
        ->generalizedTime('thisUpdate')
        ->sequence('nextUpdateSeq', 0)
            ->generalizedTime('nextUpdate')
        ->end()
        ->Extensions('singleExtensions', 1)
    );
$single->nextUpdateSeq->setOptional();
$single->singleExtensions->setOptional();

\PEAR2\Pyrus\DER\Schema::addType('AttributeTypeAndValue',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('AttributeTypeAndValue')
        ->objectIdentifier('attribute')
        ->any('value')
    );

\PEAR2\Pyrus\DER\Schema::addType('RelativeDistinguishedName',
    $rdn = \PEAR2\Pyrus\DER\Schema::factory()
    ->set('RelativeDistinguishedName')
        ->AttributeTypeAndValue('atts')
    );
$rdn->atts->setMultiple();

\PEAR2\Pyrus\DER\Schema::addType('RDNSequence',
    $rdns = \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('RDNSequence')
        ->RelativeDistinguishedName('RDN')
    );
$rdns->RDN->setMultiple();

\PEAR2\Pyrus\DER\Schema::addType('NameSeq',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('NameSeq')
        ->RDNSequence('Name')
    );

\PEAR2\Pyrus\DER\Schema::addType('Name',
    $name = clone $rdns
    );

\PEAR2\Pyrus\DER\Schema::addType('ResponderID',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->choice('ResponderID')
        ->option('byName', 'NameSeq', 1)
        ->option('byKey', 'octetString', 2)
    );

\PEAR2\Pyrus\DER\Schema::addType('ResponseData',
    $responsedata = \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('ResponseData')
        ->integer('version', 0)
        ->ResponderID('responder')
        ->generalizedTime('producedAt')
        ->sequence('responses')
            ->SingleResponse('response')
        ->end()
        ->Extensions('responseExtensions', 1)
        );
$responsedata->version->setOptional();
$responsedata->responseExtensions->setOptional();
$responsedata->responses->response->setMultiple();

\PEAR2\Pyrus\DER\Schema::addType('SubjectPublicKeyInfo',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('SubjectPublicKeyInfo')
        ->AlgorithmIdentifier('algorithm')
        ->bitString('subjectPublicKey')
    );

\PEAR2\Pyrus\DER\Schema::addType('Time',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->choice('Time')
        ->UTCTime('utc')
        ->generalizedTime('general')
    );

\PEAR2\Pyrus\DER\Schema::addType('Validity',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('Validity')
        ->Time('notBefore')
        ->Time('notAfter')
    );

\PEAR2\Pyrus\DER\Schema::addType('TBSCertificate',
    $cert = \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('TBSCertificate')
        ->sequence('versionSeq', 0)
            ->integer('version')
        ->end()
        ->integer('serialNumber')
        ->AlgorithmIdentifier('signature')
        ->Name('issuer')
        ->Validity('valid')
        ->Name('subject')
        ->SubjectPublicKeyInfo('subjectPublicKey')
        ->bitString('issuerUniqueID', 1)
        ->bitString('subjectUniqueID', 2)
        ->Extensions('extensions', 3)
    );
$cert->versionSeq->setOptional();
$cert->issuerUniqueID->setOptional();
$cert->subjectUniqueID->setOptional();
$cert->extensions->setOptional();

\PEAR2\Pyrus\DER\Schema::addType('Certificate',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('Certificate')
        ->TBSCertificate('cert')
        ->AlgorithmIdentifier('signatureAlgorithm')
        ->bitString('signature')
    );

\PEAR2\Pyrus\DER\Schema::addType('BasicOCSPResponse',
    $basic = \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('BasicOCSPResponse')
        ->ResponseData('tbsResponseData')
        ->AlgorithmIdentifier('signatureAlgorithm')
        ->bitString('signature')
        ->sequence('certs', 0)
            ->sequence('certSequence')
                ->Certificate('cert')
            ->end()
        ->end()
        );
$basic->certs->setOptional();
$basic->certs->certSequence->setMultiple();

\PEAR2\Pyrus\DER\Schema::addType('ResponseBytes',
    \PEAR2\Pyrus\DER\Schema::factory()
    ->sequence('ResponseBytes')
        ->sequence('internal')
            ->objectIdentifier('responseType')
            ->octetString('response')
        ->end()
    );

$schema = new \PEAR2\Pyrus\DER\Schema;
$schema
        ->sequence('OCSPResponse')
            ->enumerated('responseStatus')
            ->ResponseBytes('responseInfo', 0);
$schema->OCSPResponse->responseInfo->setOptional();
$der->setSchema($schema);

$basicschema = new \PEAR2\Pyrus\DER\Schema;
$basicschema
    ->BasicOCSPResponse('basicresponse');

try {
    $der->parseFromString(file_get_contents(__DIR__ . '/ocsp/response.ocsp'));
    $test->assertEquals('
 OCSPResponse [sequence]: 
  responseStatus [enumerated] (0)
  responseInfo [sequence]: 
   internal [sequence]: 
    responseType [objectIdentifier] (1.3.6.1.5.5.7.48.1.1 [OCSP basic response])
    response [octetString] (308206073082011ea17e307c310b3009060355040613024155310c300a060355040813034e5357310f300d060355040713065379646e657931143012060355040a130b43416365727420496e632e311530130603550403130c436c6173732031204f4353503121301f06092a864886f70d0109011612737570706f7274406361636572742e6f7267180f32303039303632303230343131395a30663064303c300906052b0e03021a050004148ba4c9cb172919453ebb8e730991b925f2832265041416b5321bd4c7f3e0e68ef3bdd2b03aeeb23918d102030707f48000180f32303039303632303139353235365aa011180f32303039303632303230353131395aa1233021301f06092b060105050730010204120410139ca1bc5dbc7cb422d547b6d730ca87300d06092a864886f70d010105050003818100cbd03053302ad4af3438f3f8e4dd2de1187c31fd3c073e042739959581fc194ce1618ed1c149d1466782e8f5a7dbf7e6bdc83e04270b4103944967442b29e48a4752c324bed9f5ef91ecf5a3b8cf0d4eab7fd283766aeed54d0633e594088e2f9b2ffba1bcabdfd663d869646e508e7c3bdd25c500fc17fba4939200e1af9d5ca082044e3082044a308204463082022ea003020102020302961a300d06092a864886f70d010105050030793110300e060355040a1307526f6f74204341311e301c060355040b1315687474703a2f2f7777772e6361636572742e6f7267312230200603550403131943412043657274205369676e696e6720417574686f726974793121301f06092a864886f70d0109011612737570706f7274406361636572742e6f7267301e170d3036303832323037313332345a170d3131303832323037313332345a307c310b3009060355040613024155310c300a060355040813034e5357310f300d060355040713065379646e657931143012060355040a130b43416365727420496e632e311530130603550403130c436c6173732031204f4353503121301f06092a864886f70d0109011612737570706f7274406361636572742e6f726730819f300d06092a864886f70d010101050003818d0030818902818100e18dffc8179edee691fd91801c0adee1a418ec211cf71a8abc010b232e910db8cd73e0c39f51697e1c3933eff4e7ffce3c871a1f058be7da13723488653143bb30f39270a78afb9c4c0b1bb5720ca2279a16268a6da6780d86e86df0b719d9cda77e9087274b4e0cc38cdd6fb8daed7f01353c45f5b2ad7c449252dac67038b50203010001a3583056300c0603551d130101ff0402300030270603551d250420301e06082b0601050507030206082b0601050507030106082b06010505070309301d0603551d11041630148112737570706f7274406361636572742e6f7267300d06092a864886f70d0101050500038202010034eae4996f0a3dad5c521c7805b2a7df7e6b0037d339a9111a7ca60cc7c0c8048ae0716cacdbf1307c0c56b7bd4ed4bbd39d6fea2a16006752ff0a68e80b00e586d585d8a2f8fb8de6382cbfd5a734f7181b495ea82076bca9842dfdd704ef4e4483ed8d94da22cb45433473a5a66f4dfc765e613efa6a8de644e0eead246d34ae441e3931bb1ade3331388c0706fe69c127e220fb5b0afe7bea619145643b61d5152921c8fde97ad93446071b04d4185a6da0dfb6837029c58e67cc99fb3ed194e7e707679db40918abdde2d25723326b8e78460146895d952ef611ce445166ac72e711e4f7bbb0910537fc0d1b89ee6e2289f724870ff4548533e417bfff777d7ff449bb7f1097bb6fd8a91bd1863ef033f505156488e40ea686519d5264b44c7fe1b83b5375af9de8de061ed1f8b39cfc39dbf7ac70e158b20177ff6d866405c137dd404289a64410df06a968ccdcb44abe8dc5fffcd251941f249588b0bfdf78689d72213e573cfeef0b76260b54d7299dab6c54d5ec9553888a421a032e396cb16d094e6acb615645caedc9d14573b56e1d287f7e034212b7472a956507591af666c28995fcc8122f6f2f355959bfb1b7f5f3e5e8bf731f88dacdd94e5a304a3d8d585b7954654bcb42f1c027b2ac2ecdfc4dc8851f0dc7f554225e1a010d7fd47a5c411893ad5e653d16aeae402d998ef5ee32de662107cfd8ce9f8963)
   end internal
  end responseInfo
 end OCSPResponse
end 
', (string) $der, 'after parsing');
    $data = $der->OCSPResponse->responseInfo->internal->response->getValue();
    $der = new \PEAR2\Pyrus\DER;
    $der->setSchema($basicschema);
    $der->parseFromString($data);
    $test->assertEquals('
 basicresponse [sequence]: 
  tbsResponseData [sequence]: 
   responder [sequence]: 
    Name [sequence]: 
    (multiple): 
     RDN [set]: 
      atts [sequence]: 
       attribute [objectIdentifier] (2.5.4.6 [Country Name])
       value [printableString] (AU)
      end atts
     end RDN

     RDN [set]: 
      atts [sequence]: 
       attribute [objectIdentifier] (2.5.4.8 [State/Province Name])
       value [printableString] (NSW)
      end atts
     end RDN

     RDN [set]: 
      atts [sequence]: 
       attribute [objectIdentifier] (2.5.4.7 [Locality (City) Name])
       value [printableString] (Sydney)
      end atts
     end RDN

     RDN [set]: 
      atts [sequence]: 
       attribute [objectIdentifier] (2.5.4.10 [Organization Name])
       value [printableString] (CAcert Inc.)
      end atts
     end RDN

     RDN [set]: 
      atts [sequence]: 
       attribute [objectIdentifier] (2.5.4.3 [Common Name])
       value [printableString] (Class 1 OCSP)
      end atts
     end RDN

     RDN [set]: 
      atts [sequence]: 
       attribute [objectIdentifier] (1.2.840.113549.1.9.1 [Email (for use in signatures)])
       value [iA5String] (support@cacert.org)
      end atts
     end RDN
    end (multiple)

    end Name
   end responder

   producedAt [generalizedTime] (20090620204119Z)
   responses [sequence]: 
    response [sequence]: 
     cert [sequence]: 
      hashAlgorithm [sequence]: 
       algorithm [objectIdentifier] (1.3.14.3.2.26 [SHA-1 hash algorithm])
       parameters [null] ()
      end hashAlgorithm

      issuerNameHash [octetString] (8ba4c9cb172919453ebb8e730991b925f2832265)
      issuerKeyHash [octetString] (16b5321bd4c7f3e0e68ef3bdd2b03aeeb23918d1)
      serialNumber [integer] (460788)
     end cert

     status [null] ()
     thisUpdate [generalizedTime] (20090620195256Z)
     nextUpdateSeq [sequence]: 
      nextUpdate [generalizedTime] (20090620205119Z)
     end nextUpdateSeq
    end response
   end responses

   responseExtensions [sequence]: 
    Inner [sequence]: 
     Extension [sequence]: 
      extnID [objectIdentifier] (1.3.6.1.5.5.7.48.1.2 [OCSP nonce])
      extnValue [octetString] (0410139ca1bc5dbc7cb422d547b6d730ca87)
     end Extension
    end Inner
   end responseExtensions
  end tbsResponseData

  signatureAlgorithm [sequence]: 
   algorithm [objectIdentifier] (1.2.840.113549.1.1.5 [SHA-1 checksum with RSA encryption])
   parameters [null] ()
  end signatureAlgorithm

  signature [bitString] (11001011110100001100001010011110000101010110101001010111111010011100011110011111110001110010011011101101101111000011100011111001100011111110111110011111111010010011111100110010101100101011000000111111100110011001100111000011100001100011101101000111000001100100111010001100011011001111000001011101000111101011010011111011011111101111110011010111101110010001111101001001111011100000111100101001001001110011110001001010111010011110010010001010100011110100101100001110010010111110110110011111010111101111100100011110110011110101101000111011100011001111110110011101010101111111111101001010000011111011011010101110111011010101100110111011001111100101100101001000100011101011111001101110111111111011101000011011110010101011110111111101011011000111101100011010011100100110111010100001000111011111001110111101110110010111000101011111100101111111101110100100100100111001001001110000110101111100111011011100)
  certs [sequence]: 
   certSequence [sequence]: 
    cert [sequence]: 
     cert [sequence]: 
      versionSeq [sequence]: 
       version [integer] (2)
      end versionSeq

      serialNumber [integer] (169498)
      signature [sequence]: 
       algorithm [objectIdentifier] (1.2.840.113549.1.1.5 [SHA-1 checksum with RSA encryption])
       parameters [null] ()
      end signature

      issuer [sequence]: 
      (multiple): 
       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.10 [Organization Name])
         value [printableString] (Root CA)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.11 [Organization Web Site])
         value [printableString] (http://www.cacert.org)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.3 [Common Name])
         value [printableString] (CA Cert Signing Authority)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (1.2.840.113549.1.9.1 [Email (for use in signatures)])
         value [iA5String] (support@cacert.org)
        end atts
       end RDN
      end (multiple)

      end issuer

      valid [sequence]: 
       notBefore [uTCTime] (060822071324Z)
       notAfter [uTCTime] (110822071324Z)
      end valid

      subject [sequence]: 
      (multiple): 
       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.6 [Country Name])
         value [printableString] (AU)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.8 [State/Province Name])
         value [printableString] (NSW)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.7 [Locality (City) Name])
         value [printableString] (Sydney)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.10 [Organization Name])
         value [printableString] (CAcert Inc.)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (2.5.4.3 [Common Name])
         value [printableString] (Class 1 OCSP)
        end atts
       end RDN

       RDN [set]: 
        atts [sequence]: 
         attribute [objectIdentifier] (1.2.840.113549.1.9.1 [Email (for use in signatures)])
         value [iA5String] (support@cacert.org)
        end atts
       end RDN
      end (multiple)

      end subject

      subjectPublicKey [sequence]: 
       algorithm [sequence]: 
        algorithm [objectIdentifier] (1.2.840.113549.1.1.1 [RSA encryption])
        parameters [null] ()
       end algorithm

       subjectPublicKey [bitString] (110000100000011000100110100000011000000101110000110001101111111111100100010111100111101101111011100110100100011111110110010001100000001110010101101111011100001101001001100011101100100001111001111011111010100010101011110011011100011101110100100011101101110001100110111100111110000011000011100111111010001110100111111101110011100111001111101111111101001110011111111111110011101111001000011111010111111011000101111100111110110101001111100101101001000100011001011100011000011101110111100001111001110010010111000010100111100010101111101110011100100110010111101110110101111001011001010001010011110011010101101001101000101011011011010011011110001101100001101110100011011011111000010110111110011101100111001101101001111111110100100001000011110011110010111001110110011000011100011001101110111011111011100011011010111011011111111111010111110010001011111010110110010101011011111100100010010010010101001011011010110001101110000111000101101011011101)
      end subjectPublicKey

      extensions [sequence]: 
       Inner [sequence]: 
       (multiple): 
        Extension [sequence]: 
         extnID [objectIdentifier] (2.5.29.19 [Basic Constraints - can it be a CA?])
         critical [boolean] (TRUE)
         extnValue [octetString] (3000)
        end Extension

        Extension [sequence]: 
         extnID [objectIdentifier] (2.5.29.37 [Extended Key Usage])
         extnValue [octetString] (301e06082b0601050507030206082b0601050507030106082b06010505070309)
        end Extension

        Extension [sequence]: 
         extnID [objectIdentifier] (2.5.29.17 [Subject Alternative Name])
         extnValue [octetString] (30148112737570706f7274406361636572742e6f7267)
        end Extension
       end (multiple)

       end Inner
      end extensions
     end cert

     signatureAlgorithm [sequence]: 
      algorithm [objectIdentifier] (1.2.840.113549.1.1.5 [SHA-1 checksum with RSA encryption])
      parameters [null] ()
     end signatureAlgorithm

     signature [bitString] (11010011101010111001001001100111011111010111101101011011011100101001011100111100010110110010101001111101111111111101101011011011111010011111001101010011000111010111110010100110110011000111110000001100100010010001010111000001110001110110010101100110110111111000111000011111001100101011010110111101111011001110110101001011101111010011100111011101111111010101010101011001100111101001011111111101011010001110100010110111001011000011011010101100001011101100010100010111110001111101110001101111001101110001011001011111111010101101001111101001111011111000110111001001101111010101000100000111011010111100101010011000010010110111111101110101111001110111110011101000100100000111110110110001101100101001101101010001011001011100010110000111101001110011101001011010011011011111001101111111001110110101111011000011111101111101011010101000110111100110100010011100000111011101010110110010011011011101001010111010001001111011100111000110111011110101101111011001111000111100010001100111110111111101101001110000011001111110001010000011111011101101110101111111011110111110101011000011001000110001011100100111011110000111010101101011010011000011100100011111101111010011111010110110011101001000110111110111001101010011000101101011011011010000011011111101101101000001111100001010011100010110001110110011111001100100110011111101111111011010001100101001110011111100111111110011110011101101101001001110001010101111011101111000101101001010101111000111100101101011100011101111000100011011000110100010011011101100101011011101111011010001110011101000100101000111001101010110011100101110011110001111001001111011110111011101100001001000110111011111111100110111011100010011110111011011101000101000100111110111100100100001111111111101001010100100001011100111110010010111101111111111111111101111111101111111111110100100100110111011111111110000100101111011101111011111101100010101001110111101000110000110111110111100001100111111010110110101110010010001000111001001110101001101000011010100011001110110100101100100101101001001100111111111100001101110001110111010011111010110101111100111011110100011011110110111101101000111111000101100111001110011111100111001110110111111011110101100111000011100001101100010110010111101111111111111011011000011011001001011100000111011111011101100000010000101000100110100110100010010000110111111101010100111010001100110011011100101101001001010101111101000110111000101111111111111110011010010101000110010100111111001001001010110001000101100001011111111011111111100011010001001110111100101000011111101010111111100111111101110111110111110110100110101110101001101011110100110011101101010111101100101010011010101111011001001010110100111000100010001010100001011010111011101110011101100101100011101101100110011101101010110010111100001101011010001011100101011101101110010011101000110001011110011101101011101110111011010001111111111111011100001010010101101111000111101010100101011100101111101100111010111101101100110110000101000100110010101111111001100100010010101111110111110111111010110110011011001101111111011000110110111111101011111001111100101111010001011111111100111111110001000110110101100110111011001100111010110101100001001010111101100011011011000101101111110011010100110010110010111100101110000101111000111000000100111101100101010110010111011001101111111001001101110010001000010111111110111000111111101011010100100010101111011010111011111111110101001111010101110010000011100010010011101011011011110110010111110110110101011101010111010000001011011001100110001110111101011110111011001011011110110011010000111111001111110110001100111010011111100010011100011)
    end cert
   end certSequence
  end certs
 end basicresponse
end 
', (string) $der, 'after parsing basic');
} catch (Exception $e) {
    echo $e->getMessage(), "\n", $der;
}
?>
===DONE===
--EXPECT--
===DONE===