<?php
/**
 * \pear2\Pyrus\Channel\Remotepackage
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Remote REST iteration handler
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\Channel;
class Remotepackage extends \pear2\Pyrus\PackageFile\v2 implements \ArrayAccess, \Iterator
{
    /**
     * openssl CA authorities whose certs we have
     */
    protected static $authorities = array (
                0 => 'AAACertificateServices',
                1 => 'ABA.ECOMRootCA',
                2 => 'AOLTimeWarnerRootCertificationAuthority1',
                3 => 'AOLTimeWarnerRootCertificationAuthority2',
                4 => 'AddTrustClass1CARoot',
                5 => 'AddTrustExternalCARoot',
                6 => 'AddTrustPublicCARoot',
                7 => 'AddTrustQualifiedCARoot',
                8 => 'AmericaOnlineRootCertificationAuthority1',
                9 => 'AmericaOnlineRootCertificationAuthority2',
                10 => 'AutoridaddeCertificacionFirmaprofesionalCIFA62634068',
                11 => 'BaltimoreCyberTrustRoot',
                12 => 'DigitalSignatureTrustCo.GlobalCA1',
                13 => 'DigitalSignatureTrustCo.GlobalCA3',
                14 => 'EquifaxSecureCA',
                15 => 'EquifaxSecureeBusinessCA2',
                16 => 'GoDaddyClass2CA',
                17 => 'RSASecurity1024v3',
                18 => 'RSASecurity2048v3',
                19 => 'SecurityCommunicationRootCA',
                20 => 'StarfieldClass2CA',
                21 => 'TCTrustCenter,Germany,Class2CA',
                22 => 'TCTrustCenter,Germany,Class3CA',
                23 => 'TDCInternetRootCA',
                24 => 'TaiwanGRCA',
                25 => 'Verisign-RSASecureServerCA',
                26 => 'VerisignClass1PublicPrimaryCertificationAuthority',
                27 => 'VerisignClass1PublicPrimaryCertificationAuthority-G2',
                28 => 'VerisignClass2PublicPrimaryCertificationAuthority',
                29 => 'VerisignClass2PublicPrimaryCertificationAuthority-G2',
                30 => 'VerisignClass3PublicPrimaryCertificationAuthority',
                31 => 'VerisignClass3PublicPrimaryCertificationAuthority-G2',
                32 => 'VerisignClass4PublicPrimaryCertificationAuthority-G2',
                33 => 'CACertSigningAuthority',
                34 => 'COMODOCertificationAuthority',
                35 => 'CertumCA',
                36 => 'ChambersofCommerceRoot',
                37 => 'Class2PrimaryCA',
                38 => 'DSTACESCAX6',
                39 => 'DSTRootCAX1',
                40 => 'DSTRootCAX2',
                41 => 'DSTRootCAX3',
                42 => 'DigiCertAssuredIDRootCA',
                43 => 'DigiCertGlobalRootCA',
                44 => 'DigiCertHighAssuranceEVRootCA',
                45 => 'DigiNotarRootCA',
                46 => 'Entrust.netCertificationAuthority(2048)',
                47 => 'Entrust.netClientCertificationAuthority',
                48 => 'Entrust.netClientCertificationAuthority2',
                49 => 'Entrust.netSecureServerCertificationAuthority',
                50 => 'Entrust.netSecureServerCertificationAuthority2',
                51 => 'EntrustRootCertificationAuthority',
                52 => 'EquifaxSecureGlobaleBusinessCA-1',
                53 => 'EquifaxSecureeBusinessCA-1',
                54 => 'FreeSSLCertificationAuthority',
                55 => 'GPRoot2',
                56 => 'GTECyberTrustGlobalRoot',
                57 => 'GTECyberTrustRoot',
                58 => 'GeoTrustGlobalCA',
                59 => 'GeoTrustGlobalCA2',
                60 => 'GeoTrustPrimaryCertificationAuthority',
                61 => 'GeoTrustUniversalCA',
                62 => 'GeoTrustUniversalCA2',
                63 => 'GlobalChambersignRoot',
                64 => 'GlobalSign',
                65 => 'GlobalSign2',
                66 => 'GlobalSignExtendedValidationCA',
                67 => 'GlobalSignRootCA',
                68 => 'GoDaddySecureCertificationAuthority',
                69 => 'IPSCACLASE1CertificationAuthority',
                70 => 'IPSCACLASE3CertificationAuthority',
                71 => 'IPSCACLASEA1CertificationAuthority',
                72 => 'IPSCACLASEA3CertificationAuthority',
                73 => 'IPSCAChainedCAsCertificationAuthority',
                74 => 'IPSCATimestampingCertificationAuthority',
                75 => 'IPSSERVIDORES',
                76 => 'NetLockExpressz(ClassC)Tanusitvanykiado',
                77 => 'NetLockKozjegyzoi(ClassA)Tanusitvanykiado',
                78 => 'NetLockMinositettKozjegyzoi(ClassQA)Tanusitvanykiado',
                79 => 'NetLockUzleti(ClassB)Tanusitvanykiado',
                80 => 'NetworkSolutionsCertificateAuthority',
                81 => 'QuoVadisRootCA2',
                82 => 'QuoVadisRootCA3',
                83 => 'QuoVadisRootCertificationAuthority',
                84 => 'SecureCertificateServices',
                85 => 'SecureGlobalCA',
                86 => 'SecureTrustCA',
                87 => 'SoneraClass1CA',
                88 => 'SoneraClass2CA',
                89 => 'StaatderNederlandenRootCA',
                90 => 'StartComCertificationAuthority',
                91 => 'SwissSignGoldCA-G2',
                92 => 'SwissSignPlatinumCA-G2',
                93 => 'SwissSignSilverCA-G2',
                94 => 'SwisscomRootCA1',
                95 => 'TDCOCESCA',
                96 => 'ThawtePersonalBasicCA',
                97 => 'ThawtePersonalFreemailCA',
                98 => 'ThawtePersonalPremiumCA',
                99 => 'ThawtePremiumServerCA',
                100 => 'ThawteSGCCA',
                101 => 'ThawteServerCA',
                102 => 'ThawteTimestampingCA',
                103 => 'TrustedCertificateServices',
                104 => 'TURKTRUST',
                105 => 'TURKTRUST2',
                106 => 'UTN-DATACorpSGC',
                107 => 'UTN-USERFirst-ClientAuthenticationandEmail',
                108 => 'UTN-USERFirst-Hardware',
                109 => 'UTN-USERFirst-NetworkApplications',
                110 => 'UTN-USERFirst-Object',
                111 => 'VeriSignClass1PublicPrimaryCertificationAuthority-G3',
                112 => 'VeriSignClass2PublicPrimaryCertificationAuthority-G3',
                113 => 'VeriSignClass3PublicPrimaryCertificationAuthority-G3',
                114 => 'VeriSignClass3PublicPrimaryCertificationAuthority-G5',
                115 => 'VeriSignClass4PublicPrimaryCertificationAuthority-G3',
                116 => 'VeriSignTimeStampingAuthorityCA',
                117 => 'VisaeCommerceRoot',
                118 => 'WellsFargoRootCertificateAuthority',
                119 => 'XRampGlobalCertificationAuthority',
                120 => 'beTRUSTedRootCA',
                121 => 'beTRUSTedRootCA-BaltimoreImplementation',
                122 => 'beTRUSTedRootCA-EntrustImplementation',
                123 => 'beTRUSTedRootCA-RSAImplementation',
                124 => 'thawtePrimaryRootCA',
                125 => 'valicert.com',
                126 => 'valicert.com2',
                127 => 'valicert.com3',
            );
    protected $parent;
    protected $rest;
    protected $releaseList;
    protected $remotedeps;
    protected $remoteAbridgedInfo;
    protected $versionSet = false;
    protected $minimumStability;
    protected $explicitVersion = false;
    protected $fullPackagexml = false;
    /**
     * Flag used to determine whether this package has been tested for upgradeability
     */
    protected $isUpgradeable = null;

    static function authorities()
    {
        static $authorities = null;
        if ($authorities) {
            return $authorities;
        }
        $d = \pear2\Pyrus\Main::getDataPath() . DIRECTORY_SEPARATOR . 'x509rootcerts';
        // for running out of svn
        if (!file_exists($d)) {
            $d = realpath(__DIR__ . '/../../../data/x509rootcerts');
        } else {
            if (strpos($d, 'phar://') === 0) {
                if (!file_exists($temp = \pear2\Pyrus\Config::current()->temp_dir .
                                 DIRECTORY_SEPARATOR . 'x509rootcerts')) {
                    mkdir($temp, 0755, true);
                }
                // openssl can't process these from within a phar (pity)
                foreach (static::$authorities as $i => $authority) {
                    copy($d . DIRECTORY_SEPARATOR . $authority, $temp . DIRECTORY_SEPARATOR . $authority);
                    $authorities[$i] = $temp . DIRECTORY_SEPARATOR . $authority;
                }
                return $authorities;
            }
        }
        $authorities = static::$authorities;
        foreach ($authorities as $i => $authority) {
            $authorities[$i] = $d . DIRECTORY_SEPARATOR . $authority;
        }
        return $authorities;
    }

    function __construct(\pear2\Pyrus\ChannelFileInterface $channelinfo, $releases = null)
    {
        $this->parent = $channelinfo;
        if (!isset($this->parent->protocols->rest['REST1.0'])) {
            throw new \pear2\Pyrus\Channel\Exception('Cannot access remote packages without REST1.0 protocol');
        }
        // instruct parent::__set() to call $this->setRawVersion() when setting rawversion
        $this->rawMap['rawversion'] = array('setRawVersion');
        $this->rest = new \pear2\Pyrus\REST;
        $this->releaseList = $releases;
        $this->minimumStability = \pear2\Pyrus\Config::current()->preferred_state;
        $this->explicitVersion = false;
    }

    /**
     * Sets the minimum stability allowed.
     *
     * This is set by a call to a package such as "pyrus install Pname-stable"
     * or "pyrus install Pname-beta"
     *
     * The stability is only changed if it is less stable than preferred_state.
     * @param string
     */
    function setExplicitState($stability)
    {
        $states = \pear2\Pyrus\Installer::betterStates($this->minimumStability);
        $newstates = \pear2\Pyrus\Installer::betterStates($stability);
        if (count($newstates) > count($states)) {
            $this->minimumStability = $stability;
        }
    }

    function setExplicitVersion($version)
    {
        $this->explicitVersion = $version;
    }

    function getExplicitVersion()
    {
        return $this->explicitVersion;
    }

    function resetConcreteVersion()
    {
        $this->versionSet = false;
    }

    function hasConcreteVersion()
    {
        return $this->versionSet;
    }

    function setUpgradeable()
    {
        $this->isUpgradeable = true;
    }

    function isUpgradeable()
    {
        return $this->isUpgradeable;
    }

    function isPlugin()
    {
        return false; // until there is some REST in place, we have to return false
    }

    function setRawVersion($var, $value)
    {
        if (isset($this->parent->protocols->rest['REST1.3'])) {
            $a = $this->remoteAbridgedInfo = $this->rest->retrieveCacheFirst(
                                                        $this->parent->protocols->rest['REST1.3']->baseurl .
                                                        'r/' . strtolower($this->name) . '/v2.' . $value['release'] . '.xml');
            $this->packageInfo['version']['api'] = $a['a'];
        } else {
            $a = $this->remoteAbridgedInfo = $this->rest->retrieveCacheFirst(
                                                        $this->parent->protocols->rest['REST1.0']->baseurl .
                                                        'r/' . strtolower($this->name) . '/' . $value['release'] . '.xml');
        }
        $this->packageInfo['version'] = $value;
        $this->stability['release'] = $a['st'];
        $this->license['name'] = $a['l'];
        $this->summary = $a['s'];
        $this->description = $a['d'];
        list($this->date, $this->time) = explode(' ', $a['da']);
        $this->notes = $a['n'];
        $this->versionSet = true;
    }
    
    function getDownloadURL($ext = '')
    {
        if (!$this->versionSet) {
            // this happens when doing a simple download outside of an install
            $this->rewind();
            $ok = \pear2\Pyrus\Installer::betterStates($this->minimumStability, true);
            foreach ($this->releaseList as $versioninfo) {
                if (isset($versioninfo['m'])) {
                    // minimum PHP version required
                    if (version_compare($versioninfo['m'], $this->getPHPVersion(), '>=')) {
                        continue;
                    }
                }

                if (!in_array($versioninfo['s'], $ok) && !isset(\pear2\Pyrus\Main::$options['force'])) {
                    // release is not stable enough
                    continue;
                }
                $this->version['release'] = $versioninfo['v'];
                break;
            }
        }
        return $this->remoteAbridgedInfo['g'] . $ext;
    }

    function download()
    {
        
        $url = $this->getDownloadURL();
        $errs = new \pear2\MultiErrors;

        $certdownloaded = false;
        if (extension_loaded('openssl')) {
            // try to download openssl x509 signature certificate for our release
            try {
                $cert = \pear2\Pyrus\Main::download($url . '.pem');
                $cert = $cert->body;
                $certdownloaded = true;
            } catch (\pear2\Pyrus\HTTPException $e) {
                // file does not exist, ignore
            }
            if ($certdownloaded) {
                $info = openssl_x509_parse($cert);
                if (!$info) {
                    throw new \pear2\Pyrus\Package\Exception(
                        'Invalid abstract package ' .
                        $this->channel . '/' .
                        $this->name . ' - releasing maintainer\'s certificate is not a certificate');
                }
                if (true !== openssl_x509_checkpurpose($cert, X509_PURPOSE_SSL_SERVER,
                                                       self::authorities())) {
                    throw new \pear2\Pyrus\Package\Exception(
                        'Invalid abstract package ' .
                        $this->channel . '/' .
                        $this->name . ' - releasing maintainer\'s certificate is invalid');
                }
                // now verify that this cert is in fact the releasing maintainer's certificate
                // by verifying that alternate name is the releaser's email address
                if (!isset($info['subject']) || !isset($info['subject']['emailAddress'])) {
                    throw new \pear2\Pyrus\Package\Exception(
                        'Invalid abstract package ' .
                        $this->channel . '/' .
                        $this->name . ' - releasing maintainer\'s certificate does not contain' .
                        ' an alternate name corresponding to the releaser\'s email address');
                }
                // retrieve releaser's email address
                
                if ($info['subject']['emailAddress'] != $this->maintainer[$this->remoteAbridgedInfo['m']]->email) {
                    throw new \pear2\Pyrus\Package\Exception(
                        'Invalid abstract package ' .
                        $this->channel . '/' .
                        $this->name . ' - releasing maintainer\'s certificate ' .
                        'alternate name does not match the releaser\'s email address ' .
                        $this->maintainer[$this->remoteAbridgedInfo['m']]->email);
                }
                $key = openssl_pkey_get_public($cert);
                $key = openssl_pkey_get_details($key);
                $key = $key['key'];
            }
        }

        // first try to download .phar, then .tgz, then .tar, then .zip
        // if a public key was downloaded, save it where ext/phar will
        // look to validate the openssl signature
        foreach (array('.phar', '.tgz', '.tar') as $ext) {
            try {
                if ($certdownloaded) {
                    if (!file_exists(\pear2\Pyrus\Config::current()->download_dir)) {
                        mkdir(\pear2\Pyrus\Config::current()->download_dir, 0755, true);
                    }
                    file_put_contents($pubkey = \pear2\Pyrus\Config::current()->download_dir .
                                      DIRECTORY_SEPARATOR . basename($url) . $ext . '.pubkey', $key);
                }
                $ret = new \pear2\Pyrus\Package\Remote($url . $ext);
                if ($certdownloaded) {
                    if ($ext == '.tar' || $ext == '.tgz') {
                        if (phpversion() == '5.3.0') {
                            \pear2\Pyrus\Logger::log(0, 'WARNING: ' . $url . $ext . ' may not be installable ' .
                                                                    'with PHP version 5.3.0, the PHP extension phar ' .
                                                                    'has a bug verifying openssl signatures for ' .
                                                                    'tar and tgz files.  Either upgrade to PHP 5.3.1 ' .
                                                                    'or install the .zip version');
                        }
                    }
                }
                return $ret;
            } catch (\pear2\Pyrus\HTTPException $e) {
                if ($certdownloaded && file_exists($pubkey)) {
                    unlink($pubkey);
                }
                $errs->E_ERROR[] = $e;
            } catch (\Exception $e) {
                if ($certdownloaded && file_exists($pubkey)) {
                    unlink($pubkey);
                }
                $errs->E_ERROR[] = $e;
                throw new \pear2\Pyrus\Package\Exception(
                    'Invalid abstract package ' .
                    $this->channel . '/' .
                    $this->name, $errs);
            }
        }

        try {
            // phar does not support signatures for zip archives
            $ret = new \pear2\Pyrus\Package\Remote($url . '.zip');
            return $ret;
        } catch (\pear2\Pyrus\HTTPException $e) {
            $errs->E_ERROR[] = $e;
            throw new \pear2\Pyrus\Package\Exception(
                'Could not download abstract package ' .
                $this->channel . '/' .
                $this->name, $errs);
        } catch (\Exception $e) {
            $errs->E_ERROR[] = $e;
            throw new \pear2\Pyrus\Package\Exception(
                'Invalid abstract package ' .
                $this->channel . '/' .
                $this->name, $errs);
        }
    }

    function offsetGet($var)
    {
        $lowerpackage = strtolower($var);
        try {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'p/' . $lowerpackage . '/info.xml');
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\Channel\Exception('package ' . $var . ' does not exist', $e);
        }
        if (is_string($this->releaseList)) {
            $ok = \pear2\Pyrus\Installer::betterStates($this->releaseList, true);
            if (isset($this->parent->protocols->rest['REST1.3'])) {
                $rinfo = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.3']->baseurl .
                                                        'r/' . $lowerpackage . '/allreleases2.xml');
            } else {
                $rinfo = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                        'r/' . $lowerpackage . '/allreleases.xml');
            }
            if (!isset($rinfo['r'][0])) {
                $rinfo['r'] = array($rinfo['r']);
            }
            $releases = array();
            foreach ($rinfo['r'] as $release) {
                if (!in_array($release['s'], $ok)) {
                    continue;
                }
                if (!isset($release['m'])) {
                    $release['m'] = '5.2.0';
                }
                $releases[] = $release;
            }
            $this->releaseList = $releases;
        }
        $pxml = clone $this;
        $pxml->channel = $info['c'];
        $pxml->name = $info['n'];
        $pxml->license = $info['l'];
        $pxml->summary = $info['s'];
        $pxml->description = $info['d'];
        return $pxml;
    }

    function offsetSet($var, $value)
    {
        throw new \pear2\Pyrus\Channel\Exception('remote channel info is read-only');
    }

    function offsetUnset($var)
    {
        throw new \pear2\Pyrus\Channel\Exception('remote channel info is read-only');
    }

    /**
     * This is very expensive, use sparingly if at all
     */
    function offsetExists($var)
    {
        try {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'p/' . strtolower($var) . '/info.xml');
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    function valid()
    {
        return current($this->releaseList);
    }

    function current()
    {
        $info = current($this->releaseList);
        if (!isset($info['m'])) {
            $info['m'] = '5.2.0'; // guess something lower than us
        }
        // setting this allows us to retrieve information specific to this
        // version
        $this->version['release'] = $info['v'];
        return array('stability' => $info['s'], 'minimumphp' => $info['m']);
    }

    function key()
    {
        $info = current($this->releaseList);
        return $info['v'];
    }

    function next()
    {
        return next($this->releaseList);
    }

    function rewind()
    {
        if (is_array($this->releaseList)) {
            return reset($this->releaseList);
        }
        if (!$this->name) {
            throw new \pear2\Pyrus\Channel\Exception('Cannot iterate without first choosing a remote package');
        }
        if (isset($this->parent->protocols->rest['REST1.3'])) {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.3']->baseurl .
                                                    'r/' . strtolower($this->name) . '/allreleases2.xml');
        } else {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'r/' . strtolower($this->name) . '/allreleases.xml');
        }
        $this->releaseList = $info['r'];
        if (!isset($this->releaseList[0])) {
            $this->releaseList = array($this->releaseList);
        }
    }

    function getReleaseList()
    {
        $this->rewind();
        return $this->releaseList;
    }

    function getDependencies()
    {
        // dynamically retrieve the dependencies from the remote server when requested
        $deps = unserialize($this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'r/' . strtolower($this->name) . '/deps.' .
                                                    $this->version['release'] . '.txt'));
        if ($deps) {
            $this->packageInfo['dependencies'] = $deps;
        }
        return parent::getDependencies();
    }

    function getMaintainer()
    {
        // can't get email addresses from REST, have to grab the entire package.xml
        $this->grabEntirePackagexml();
        return parent::getMaintainer();
    }

    function getPackagefileObject()
    {
        return $this;
    }

    /**
     * This is used to download the entire package.xml, which is useful
     * for commands such as the info command.
     */
    function grabEntirePackagexml()
    {
        if ($this->fullPackagexml) {
            return;
        }
        if (!$this->explicitVersion) {
            $fakedep = new \pear2\Pyrus\PackageFile\v2\Dependencies\Package(
                'required', 'package', null, array('name' => $this->name, 'channel' => $this->channel, 'uri' => null,
                                            'min' => null, 'max' => null,
                                            'recommended' => null, 'exclude' => null,
                                            'providesextension' => null, 'conflicts' => null), 0);
            $this->figureOutBestVersion($fakedep);
        }
        $pxml = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                'r/' . strtolower($this->name) . '/package.' .
                                                $this->version['release'] . '.xml');
        $this->fromArray(array('package' => $pxml));
        $this->fullPackagexml = true;
    }

    /**
     * For unit testing purposes
     */
    function getPHPVersion()
    {
        return phpversion();
    }

    function getAllUpgrades($currentversion)
    {
        // set up release list if not done yet
        $this->rewind();
        $ok = \pear2\Pyrus\Installer::betterStates($this->minimumStability, true);
        $ret = array();
        foreach ($this->releaseList as $versioninfo) {
            if (isset($versioninfo['m'])) {
                // minimum PHP version required
                if (version_compare($versioninfo['m'], $this->getPHPVersion(), '>=')) {
                    continue;
                }
            }
            if (version_compare($versioninfo['v'], $currentversion, '<=')) {
                continue;
            }
            if (!in_array($versioninfo['s'], $ok)) {
                // release is not stable enough
                continue;
            }
            $ret[] = $versioninfo;
        }
        return $ret;
    }

    /**
     * Figure out which version is best, and use this, or error out if none work
     * @param \pear2\Pyrus\PackageFile\v2\Dependencies\Package $compositeDep
     *        the composite of all dependencies on this package, as calculated
     *        by {@link \pear2\Pyrus\Package\Dependency::getCompositeDependency()}
     */
    function figureOutBestVersion(\pear2\Pyrus\PackageFile\v2\Dependencies\Package $compositeDep,
                                  $versions = null,
                                  \pear2\Pyrus\PackageFile\v2\Dependencies\Package $compositeConflictingDep = null)
    {
        // set up release list if not done yet
        $this->rewind();
        $ok = \pear2\Pyrus\Installer::betterStates($this->minimumStability, true);
        $v = $this->explicitVersion;
        $n = $this->channel . '/' . $this->name;
        $failIfExplicit = function($versioninfo) use ($v, $n) {
            if ($v && $versioninfo['v'] == $v) {
                throw new \pear2\Pyrus\Channel\Exception($n .
                                                        ' Cannot be installed, it does not satisfy ' .
                                                        'all dependencies');
            }
        };
        foreach ($this->releaseList as $versioninfo) {
            if (isset(\pear2\Pyrus\Main::$options['force'])) {
                // found one
                if ($this->versionSet && $versioninfo['v'] != $this->version['release']) {
                    // inform the installer we need to reset dependencies
                    $this->version['release'] = $versioninfo['v'];
                    return true;
                }
                $this->version['release'] = $versioninfo['v'];
                return;
            }
            if ($versions && !in_array($versioninfo['v'], $versions)) {
                continue;
            }
            if (!isset(\pear2\Pyrus\Main::$options['force']) && isset($versioninfo['m'])) {
                // minimum PHP version required
                if (version_compare($versioninfo['m'], $this->getPHPVersion(), '>=')) {
                    $failIfExplicit($versioninfo);
                    continue;
                }
            }

            if (!in_array($versioninfo['s'], $ok) && !isset(\pear2\Pyrus\Main::$options['force'])) {
                // release is not stable enough
                continue;
            }

            if ($this->explicitVersion && $versioninfo['v'] != $this->explicitVersion) {
                continue;
            }

            if (!$compositeDep->satisfied($versioninfo['v'])) {
                $failIfExplicit($versioninfo);
                continue;
            }

            if ($compositeConflictingDep && !$compositeConflictingDep->satisfied($versioninfo['v'])) {
                $failIfExplicit($versioninfo);
                continue;
            }

            $paranoia = \pear2\Pyrus\Main::getParanoiaLevel();
            if (!$this->explicitVersion && $paranoia > 1) {
                // first, we check to see if we are upgrading
                if (isset(\pear2\Pyrus\Main::$options['upgrade'])) {
                    // now we check to see if we are installed
                    if (isset(\pear2\Pyrus\Config::current()->registry->package[$n])) {
                        $installed = \pear2\Pyrus\Config::current()
                                     ->registry->info($this->name, $this->channel, 'apiversion');
                        $installed = explode('.', $installed);
                        if (count($installed) == 2) {
                            $installed[] = '0';
                        }
                        if (count($installed) == 1) {
                            $installed[] = '0';
                            $installed[] = '0';
                        }
                        if (isset($this->parent->protocols->rest['REST1.3'])) {
                            $api = $this->rest->retrieveCacheFirst(
                                $this->parent->protocols->rest['REST1.3']->baseurl .
                                'r/' . strtolower($this->name) . '/v2.' . $versioninfo['v'] . '.xml');
                        } else {
                            throw new \pear2\Pyrus\Channel\Exception('Channel ' .
                                                                    $this->channel .
                                                                    ' does not support ' .
                                                                    'a paranoia greater than 1');
                        }
                        $api = explode('.', $api['a']);
                        if (count($api) == 2) {
                            $api[] = '0';
                        }
                        if (count($api) == 1) {
                            $api[] = '0';
                            $api[] = '0';
                        }
                        if ($paranoia > 4) {
                            $paranoia = 4;
                        }
                        switch ($paranoia) {
                            case 4 :
                                if ($installed != $api) {
                                    \pear2\Pyrus\Logger::log(0,
                                        'Skipping ' . $this->channel . '/' .
                                        $this->name . ' version ' .
                                        $versioninfo['v'] . ', API has changed');
                                    continue 2;
                                }
                                break;
                            case 3 :
                                if ($installed[0] == $api[0] && $installed[1] != $api[1]) {
                                    \pear2\Pyrus\Logger::log(0,
                                        'Skipping ' . $this->channel . '/' .
                                        $this->name . ' version ' .
                                        $versioninfo['v'] . ', API has added' .
                                        ' new features');
                                    continue 2;
                                }
                                // break intentionally omitted
                            case 2 :
                                if ($installed[0] != $api[0]) {
                                    \pear2\Pyrus\Logger::log(0,
                                        'Skipping ' . $this->channel . '/' .
                                        $this->name . ' version ' .
                                        $versioninfo['v'] . ', API breaks' .
                                        ' backwards compatibility');
                                    continue 2;
                                }
                                break;
                        }
                    }
                }
            }
            // found one
            if ($this->versionSet && $versioninfo['v'] != $this->version['release']) {
                // inform the installer we need to reset dependencies
                $this->version['release'] = $versioninfo['v'];
                return true;
            }
            $this->version['release'] = $versioninfo['v'];
            return;
        }
        throw new \pear2\Pyrus\Channel\Exception('Unable to locate a package release for ' .
                                                $this->channel . '/' . $this->name .
                                                ' that can satisfy all dependencies');
    }
}