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

PEAR2_Pyrus_DER_Schema::addType('RevokedInfo',
    $revoked = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('RevokedInfo')
        ->generalizedTime('revocationTime')
        ->enumerated('revocationReason', 0)
    );
$revoked->revocationReason->setOptional();

PEAR2_Pyrus_DER_Schema::addType('CertStatus',
    PEAR2_Pyrus_DER_Schema::factory()
    ->choice('CertStatus')
        ->option('good', 'null')
        ->option('revoked', 'RevokedInfo')
        ->option('unknown', 'any')
    );

PEAR2_Pyrus_DER_Schema::addType('SingleResponse',
    $single = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('SingleResponse')
        ->CertID('cert')
        ->CertStatus('status')
        ->generalizedTime('thisUpdate')
        ->generalizedTime('nextUpdate', 0)
        ->Extensions('singleExtensions', 1)
    );
$single->nextUpdate->setOptional();
$single->singleExtensions->setOptional();

PEAR2_Pyrus_DER_Schema::addType('AttributeTypeAndValue',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('AttributeTypeAndValue')
        ->objectIdentifier('attribute')
        ->any('value')
    );

PEAR2_Pyrus_DER_Schema::addType('RelativeDistinguishedName',
    $rdn = PEAR2_Pyrus_DER_Schema::factory()
    ->set('RelativeDistinguishedName')
        ->AttributeTypeAndValue('atts')
    );
$rdn->atts->setMultiple();

PEAR2_Pyrus_DER_Schema::addType('RDNSequence',
    $rdns = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('RDNSequence')
        ->RelativeDistinguishedName('RDN')
    );
$rdns->RDN->setMultiple();

PEAR2_Pyrus_DER_Schema::addType('Name',
    PEAR2_Pyrus_DER_Schema::factory()
    ->choice('Name')
        ->option('rdn', 'RDNSequence')
    );

PEAR2_Pyrus_DER_Schema::addType('ResponderID',
    PEAR2_Pyrus_DER_Schema::factory()
    ->choice('ResponderID')
        ->option('byName', 'Name')
        ->option('byKey', 'octetString')
    );

PEAR2_Pyrus_DER_Schema::addType('ResponseData',
    $responsedata = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('ResponseData')
        ->integer('version', 0)
        ->ResponderID('responder')
        ->generalizedTime('producedAt')
        ->sequence('responses')
            ->SingleResponse('response')
        ->end()
        ->Extensions('responseExtensions', 1)
        );
$responsedata->responseExtensions->setOptional();
$responsedata->responses->response->setMultiple();

PEAR2_Pyrus_DER_Schema::addType('SubjectPublicKeyInfo',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('SubjectPublicKeyInfo')
        ->AlgorithmIdentifier('algorithm')
        ->bitString('subjectPublicKey')
    );

PEAR2_Pyrus_DER_Schema::addType('Time',
    PEAR2_Pyrus_DER_Schema::factory()
    ->choice('Time')
        ->UTCTime('utc')
        ->generalizedTime('general')
    );

PEAR2_Pyrus_DER_Schema::addType('Validity',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('Validity')
        ->Time('notBefore')
        ->Time('notAfter')
    );

PEAR2_Pyrus_DER_Schema::addType('TBSCertificate',
    $cert = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('TBSCertificate')
        ->integer('version', 0)
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
$cert->version->setOptional();
$cert->issuerUniqueID->setOptional();
$cert->subjectUniqueID->setOptional();
$cert->extensions->setOptional();

PEAR2_Pyrus_DER_Schema::addType('Certificate',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('Certificate')
        ->TBSCertificate('cert')
        ->AlgorithmIdentifier('signatureAlgorithm')
        ->bitString('signature')
    );

PEAR2_Pyrus_DER_Schema::addType('BasicOCSPResponse',
    $basic = PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('BasicOCSPResponse')
        ->ResponseData('tbsResponseData')
        ->AlgorithmIdentifier('signatureAlgorithm')
        ->bitString('signature')
        ->sequence('certs', 0)
            ->Certificate('cert')
        ->end()
        );
$basic->certs->setOptional();
$basic->certs->cert->setMultiple();

PEAR2_Pyrus_DER_Schema::addType('ResponseBytes',
    PEAR2_Pyrus_DER_Schema::factory()
    ->sequence('ResponseBytes')
        ->sequence('internal')
            ->objectIdentifier('responseType')
            ->octetString('response')
        ->end()
    );

$schema = new PEAR2_Pyrus_DER_Schema;
$schema
        ->sequence('OCSPResponse')
            ->enumerated('responseStatus')
            ->ResponseBytes('responseInfo', 0);
$schema->OCSPResponse->responseInfo->setOptional();
$der->setSchema($schema);

try {
    $der->parseFromString(file_get_contents(__DIR__ . '/ocsp/response.ocsp'));
    $test->assertEquals('
 OCSPResponse [sequence]: 
  responseStatus [enumerated] (0)
  responseInfo [sequence]: 
   internal [sequence]: 
    responseType [objectIdentifier] (1.3.6.1.5.5.7.48.1.1 [OCSP basic response])
    response [octetString] (308206073082011ea17e307c310b3009060355040613024155310c300a060355040813034e5357310f300d060355040713065379646e657931143012060355040a130b43416365727420496e632e311530130603550403130c436c6173732031204f4353503121301f06092a864886f70d0109011612737570706f7274406361636572742e6f7267180f32303039303632303230343131395a30663064303c300906052b0e03021a050004148ba4c9cb172919453ebb8e730991b925f2832265041416b5321bd4c7f3e0e68ef3bdd2b03aeeb23918d102030707f48000180f32303039303632303139353235365aa011180f32303039303632303230353131395aa1233021301f06092b060105050730010204120410139ca1bc5dbc7cb422d547b6d730ca87300d06092a864886f70d010105050003818100cbd03053302ad4af3438f3f8e4dd2de1187c31fd3c073e042739959581fc194ce1618ed1c149d1466782e8f5a7dbf7e6bdc83e04270b4103944967442b29e48a4752c324bed9f5ef91ecf5a3b8cf0d4eab7fd283766aeed54d0633e594088e2f9b2ffba1bcabdfd663d869646e508e7c3bdd25c500fc17fba4939200e1af9d5ca082044e3082044a308204463082022ea003020102020302961a300d06092a864886f70d010105050030793110300e060355040a1307526f6f74204341311e301c060355040b1315687474703a2f2f7777772e6361636572742e6f7267312230200603550403131943412043657274205369676e696e6720417574686f726974793121301f06092a864886f70d0109011612737570706f7274406361636572742e6f7267301e170d3036303832323037313332345a170d3131303832323037313332345a307c310b3009060355040613024155310c300a060355040813034e5357310f300d060355040713065379646e657931143012060355040a130b43416365727420496e632e311530130603550403130c436c6173732031204f4353503121301f06092a864886f70d0109011612737570706f7274406361636572742e6f726730819f300d06092a864886f70d010101050003818d0030818902818100e18dffc8179edee691fd91801c0adee1a418ec211cf71a8abc010b232e910db8cd73e0c39f51697e1c3933eff4e7ffce3c871a1f058be7da13723488653143bb30f39270a78afb9c4c0b1bb5720ca2279a16268a6da6780d86e86df0b719d9cda77e9087274b4e0cc38cdd6fb8daed7f01353c45f5b2ad7c449252dac67038b50203010001a3583056300c0603551d130101ff0402300030270603551d250420301e06082b0601050507030206082b0601050507030106082b06010505070309301d0603551d11041630148112737570706f7274406361636572742e6f7267300d06092a864886f70d0101050500038202010034eae4996f0a3dad5c521c7805b2a7df7e6b0037d339a9111a7ca60cc7c0c8048ae0716cacdbf1307c0c56b7bd4ed4bbd39d6fea2a16006752ff0a68e80b00e586d585d8a2f8fb8de6382cbfd5a734f7181b495ea82076bca9842dfdd704ef4e4483ed8d94da22cb45433473a5a66f4dfc765e613efa6a8de644e0eead246d34ae441e3931bb1ade3331388c0706fe69c127e220fb5b0afe7bea619145643b61d5152921c8fde97ad93446071b04d4185a6da0dfb6837029c58e67cc99fb3ed194e7e707679db40918abdde2d25723326b8e78460146895d952ef611ce445166ac72e711e4f7bbb0910537fc0d1b89ee6e2289f724870ff4548533e417bfff777d7ff449bb7f1097bb6fd8a91bd1863ef033f505156488e40ea686519d5264b44c7fe1b83b5375af9de8de061ed1f8b39cfc39dbf7ac70e158b20177ff6d866405c137dd404289a64410df06a968ccdcb44abe8dc5fffcd251941f249588b0bfdf78689d72213e573cfeef0b76260b54d7299dab6c54d5ec9553888a421a032e396cb16d094e6acb615645caedc9d14573b56e1d287f7e034212b7472a956507591af666c28995fcc8122f6f2f355959bfb1b7f5f3e5e8bf731f88dacdd94e5a304a3d8d585b7954654bcb42f1c027b2ac2ecdfc4dc8851f0dc7f554225e1a010d7fd47a5c411893ad5e653d16aeae402d998ef5ee32de662107cfd8ce9f8963)
   end
  end
 end
end
', (string) $der, 'after parsing');
} catch (Exception $e) {
    echo $e->getMessage(), "\n", $der;
}
?>
===DONE===
--EXPECT--
===DONE===