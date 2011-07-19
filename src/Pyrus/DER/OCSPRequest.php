<?php
/**
 * \Pyrus\DER\Schema
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Represents a Distinguished Encoding Rule IASN.1 schema
 *
 * This is used to name components and to retrieve context-specific types
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\DER;
class OCSPRequest extends \Pyrus\DER
{
    protected function __construct($developerCert)
    {
        $this->setSchema($this->getSchema());
        $ocsprequest = $this->OCSPRequest->tbsRequest->requestListSeq->reqCert;
        $ocsprequest->hashAlgorithm->algorithm = '1.3.14.3.2.26'; // SHA-1
        $ocsprequest->hashAlgorithm->parameters = null;

        if (!file_exists($developerCert)) {
            throw new Exception('Developer certificate ' . $developerCert . ' does not exist');
        }
        $info = openssl_x509_parse(file_get_contents($developerCert));
        if (!isset($info['serialNumber']) || !isset($info['issuer']) || !isset($info['issuer']['OU'])) {
            throw new Exception('Cannot process developer Certificate ' . $developerCert .
                                                ', missing key fields');
        }
        if ($info['issuer']['OU'] != 'http://www.cacert.org') {
            // other issuers are picky about who they allow to verify,
            // so we only accept certs from cacert
            throw new Exception('Cannot verify certificate, ' .
                                                'it is not from cacert.org');
        }
        $ocsprequest->issuerNameHash =
            pack('C*', '8ba4c9cb172919453ebb8e730991b925f2832265');
        $ocsprequest->issuerKeyHash =
            pack('C*', '16b5321bd4c7f3e0e68ef3bdd2b03aeeb23918d1');
        $ocsprequest->serialNumber = $info['serialNumber'];

        $this->requestExtensions->Inner->Extension->extnID = '1.3.6.1.5.5.7.48.1.2'; // OCSP nonce
        $this->requestExtensions->Inner->Extension->extnValue = md5($info['serialNumber'] . time(), true);
        echo $this;
    }

    protected function getSchema()
    {
        if (isset($types['ocsprequest'])) {
            return $types['OCSPRequest'];
        }

        $types = Schema::types();

        if (!isset($types['anothername'])) {
            Schema::addType('AnotherName',
                Schema::factory()->sequence('AnotherName')
                                 ->objectIdentifier('type-id')
                                 ->any('value', 0));
        }

        if (!isset($types['generalname'])) {
            Schema::addType('GeneralName',
                Schema::factory()
                ->choice('GeneralName')
                    ->option('otherName', 'AnotherName')
                    ->option('rfc822Name', 'IA5String')
                    ->option('dNSName', 'IA5String')
                    ->option('x400Address', 'AnotherName') // ORaddress, I'm lazy
                    ->option('dNSName', 'IA5String'));
        }

        if (!isset($types['algorithmidentifier'])) {
            Schema::addType('AlgorithmIdentifier',
                Schema::factory()
                ->sequence('AlgorithmIdentifier')
                    ->objectIdentifier('algorithm')
                    ->any('parameters'));
        }

        if (!isset($types['certid'])) {
            Schema::addType('CertID',
                Schema::factory()
                ->sequence('CertID')
                    ->algorithmIdentifier('hashAlgorithm')
                    ->octetString('issuerNameHash')
                    ->octetString('issuerKeyHash')
                    ->integer('serialNumber'));
        }

        if (!isset($types['extensions'])) {
            Schema::addType('Extensions',
                $extensions = Schema::factory()
                ->sequence('Extensions')
                    ->sequence('Inner')
                        ->sequence('Extension')->setMultiple()
                            ->objectIdentifier('extnID')
                            ->boolean('critical')
                            ->octetString('extnValue')
                        ->end()
                    ->end());
            $extensions->Inner->Extension->critical->setOptional();
        }

        if (!isset($types['request'])) {
            Schema::addType('Request',
                $request = Schema::factory()
                ->sequence('Request')
                    ->certID('reqCert')
                    ->extensions('singleRequestExtensions', 0));
            $request->singleRequestExtensions->setOptional();
        }

        if (!isset($types['tbsrequest'])) {
            Schema::addType('TBSRequest',
                $tbs = Schema::factory()
                ->sequence('TBSRequest')
                    ->integer('version', 0)
                    ->generalName('requestorName', 1)
                    ->sequence('requestListSeq')
                        ->request('requestList')
                    ->end()
                    ->extensions('requestExtensions', 2)
                );
            $tbs->version->setOptional();
            $tbs->requestorName->setOptional();
            $tbs->test->requestList->setMultiple();
            $tbs->requestExtensions->setOptional();
        }

        $schema = new Schema;
        $schema->sequence('OCSPRequest')->TBSRequest('tbsRequest');
        Schema::addType('OCSPRequest', $schema);
        return $schema;
    }
}
