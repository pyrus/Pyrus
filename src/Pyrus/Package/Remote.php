<?php
/**
 * PEAR2_Pyrus_Package_Remote
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
 * Class representing a remote package
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Package_Remote extends PEAR2_Pyrus_Package
{
    private $_info;
    protected $downloadInfo;
    /**
     * @param string $package path to package file
     */
    function __construct($package, PEAR2_Pyrus_Package $parent)
    {
        $this->_info = $package;
        if (!is_array($package) &&
              (preg_match('#^(http[s]?|ftp[s]?)://#', $package))) {
            $this->internal = $this->_fromUrl($package);
        } else {
            $this->internal = $this->_fromString($package);
        }
        $this->from = $parent;
    }

    /**
     * Convert this remote packagefile into a local .tar, .tgz or .phar
     *
     * @return PEAR2_Pyrus_Package_Base
     */
    function download()
    {
        if ($this->_type === 'url') {
            return $this->internal;
        }

        $internal = $this->internal;
        // first try to download zip, then tgz, then tar
        $errs = new PEAR2_MultiErrors;
        try {
            $this->internal = new PEAR2_Pyrus_Package_Remote(
                $this->downloadInfo['url'] . '.tgz', $this);
        } catch (Exception $e) {
            $errs->E_ERROR[] = $e;
        }
        if (isset($e)) {
            unset($e);
            try {
                $this->internal = new PEAR2_Pyrus_Package_Remote(
                    $this->downloadInfo['url'] . '.tar', $this);
            } catch (Exception $e) {
                $errs->E_ERROR[] = $e;
            }
        }
        if (isset($e)) {
            unset($e);
            try {
                $this->internal = new PEAR2_Pyrus_Package_Remote(
                    $this->downloadInfo['url'] . '.zip', $this);
            } catch (Exception $e) {
                $errs->E_ERROR[] = $e;
                throw new PEAR2_Pyrus_Package_Exception(
                    'Could not download abstract package ' .
                    $this->downloadInfo['info']->channel . '/' .
                    $this->downloadInfo['info']->name, $errs);
            }
        }
        $this->internal->setFrom($internal);
        return $this->internal;
    }

    private function _fromUrl($param, $saveparam = '')
    {
        $this->_type = 'url';
        // for now, we'll use without callback
//            $callback = $this->_downloader->ui ?
//                array(&$this->_downloader, '_downloadCallback') : null;
        $dir = PEAR2_Pyrus_Config::current()->download_dir;
        try {
            $http = new PEAR2_HTTP_Request($param);
            $response = $http->sendRequest();
            $name = 'unknown.tgz';
            if ($response->code == '200') {
                if (isset($response->headers['content-disposition'])) {
                    if (preg_match('/filename="(.+)"/', $response->headers['content-disposition'], $match)) {
                        $name = $match[1];
                    }
                }
                if (!@file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $response->body);
            } else {
                throw new PEAR2_Pyrus_Package_Exception('Download failed, received ' . $response->code);
            }
            // whew, download worked!
            $a = new PEAR2_Pyrus_Package($dir . DIRECTORY_SEPARATOR . $name);
            return $a->getInternalPackage();
        } catch (Exception $e) {
            if (!empty($saveparam)) {
                $saveparam = ", cannot download \"$saveparam\"";
            }
            throw new PEAR2_Pyrus_Package_Exception('Could not download from "' . $param .
                '"' . $saveparam, $e);
        }
    }

    /**
     *
     * @param string|array pass in an array of format
     *                     array(
     *                      'package' => 'pname',
     *                     ['channel' => 'channame',]
     *                     ['version' => 'version',]
     *                     ['state' => 'state',])
     *                     or a string of format [channame/]pname[-version|-state]
     */
    private function _fromString($param)
    {
        try {
            $pname = PEAR2_Pyrus_Config::parsePackageName($param, true);
        } catch (Exception $e) {
            if ($e->why !== 'channel') {
                throw new PEAR2_Pyrus_Package_Exception('Cannot process remote package', $e);
            }
//            if ($this->_downloader->discover($param['channel'])) {
                if (PEAR2_Pyrus_Config::current()->auto_discover) {
                    try {
                        $pname =
                        PEAR2_Pyrus_Config::parsePackageName($param,
                                PEAR2_Pyrus_Config::current()->default_channel);
                    } catch (Exception $e) {
                        if (is_array($param)) {
                            $param =
                              PEAR2_Pyrus_Config::parsedPackageNameToString($param);
                        }
                        throw new PEAR2_Pyrus_Package_Exception(
                            'invalid package name/package file "' . $param . '"', $e);
                    }
                } else {
//                    if (!isset($options['soft'])) {
                        PEAR2_Pyrus_Log::log(0, 'Channel "' . $param['channel'] .
                            '" is not initialized, use ' .
                            '"pyrus channel-discover ' . $param['channel'] . '" to initialize' .
                            'or pyrus config-set auto_discover 1');
//                    }
                }
//            } else {
//                throw new PEAR2_Pyrus_Package_Exception(
//                    'invalid package name/package file "' . $param . '"', $e);
//            }
        }
        $this->_parsedname = $pname;
        if (isset($pname['state'])) {
            $this->_explicitState = $pname['state'];
        } else {
            $this->_explicitState = false;
        }
        if (isset($pname['group'])) {
            $this->_explicitGroup = true;
        } else {
            $this->_explicitGroup = false;
        }
        $info = $this->getPackageDownloadUrl($pname);
        $this->analyze($info, $param, $pname);
        $this->downloadInfo = $info;
        return $info['info'];
    }

    /**
     * @param array output of {@link parsePackageName()}
     * @access private
     */
    function getPackageDownloadUrl($parr)
    {
        $curchannel = PEAR2_Pyrus_Config::current()->default_channel;
        PEAR2_Pyrus_Config::current()->default_channel = $parr['channel'];
        // getDownloadURL returns an array.  On error, it only contains information
        // on the latest release as array(version, info).  On success it contains
        // array(version, info, download url string)
        $state = isset($parr['state']) ? $parr['state'] :  PEAR2_Pyrus_Config::current()->preferred_state;
        if (!isset(PEAR2_Pyrus_Config::current()->channelregistry[$parr['channel']])) {
            do {
                if (PEAR2_Pyrus_Config::current()->auto_discover) {
                    if (PEAR2_Pyrus_Config::current()->channelregistry->discover($parr['channel'])) {
                        break;
                    }
                }
                PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
                throw new PEAR2_Pyrus_Package_Exception(
                    'Unknown remote channel: ' . $remotechannel);
            } while (false);
        }
        try {
            $chan = PEAR2_Pyrus_Config::current()->channelregistry[$parr['channel']];
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Exception('Cannot retrieve download information ' .
                'for remote abstract package ' . $parr['channel'] . '/' . $parr['package'], $e);
        }
        try {
            $version = PEAR2_Pyrus_Config::current()->registry->info($parr['package'],
                $parr['channel'], 'version');
        } catch (Exception $e) {
            $version = null;
        }
        $base2 = false;
        $mirrors = $chan->mirrors;
        if (isset($mirrors[PEAR2_Pyrus_Config::current()->preferred_mirror])) {
            $mirror = $mirrors[PEAR2_Pyrus_Config::current()->preferred_mirror];
        } else {
            $mirror = $chan;
        }
        if (!$mirror->supportsREST() ||
              !(($base2 = $mirror->getBaseURL('REST1.3')) ||
              ($base = $mirror->getBaseURL('REST1.0')))) {
            throw new PEAR2_Pyrus_Package_Exception('Cannot retrieve remote information, ' .
                'channel ' . $chan->getName() . ' does not support REST');
        }
        if ($base2) {
            $rest = new PEAR2_Pyrus_REST_13;
            $base = $base2;
        } else {
            $rest = new PEAR2_Pyrus_REST_10;
        }
        try {
            if (!isset($parr['version']) && !isset($parr['state']) && $version
                  && !isset(PEAR2_Pyrus_Installer::$options['downloadonly'])) {
                $url = $rest->getDownloadURL($base, $parr, $state, $version);
            } else {
                $url = $rest->getDownloadURL($base, $parr, $state, false);
            }
        } catch (Exception $e) {
            PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
            throw new PEAR2_Pyrus_Package_Exception('Cannot retrieve download information ' .
            'for remote abstract package ' . $parr['channel'] . '/' . $parr['package'], $e);
        }
        if ($parr['channel'] != $curchannel) {
            PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
        }
        $url['raw'] = false; // no checking is necessary for REST
        if (!is_string($url['info'])) {
            throw new PEAR2_Pyrus_Package_Exception(
                'Invalid remote dependencies retrieved from REST - ' .
                'this should never happen');
        }
        if (!isset(PEAR2_Pyrus_Installer::$options['force']) &&
              !isset(PEAR2_Pyrus_Installer::$options['downloadonly']) &&
              $version &&
              !isset($parr['group'])) {
            if (version_compare($version, $url['version'], '>=')) {
                throw new PEAR2_Pyrus_Package_InstalledException(
                    PEAR2_Pyrus_Config::parsedPackageNameToString($parr, true) .
                    ' is already installed and is newer than detected ' .
                    'release version ' . $url['version']);
            }
        }
        $parser = new PEAR2_Pyrus_PackageFile_Parser_v2;
        $pf = $parser->parse($url['info'], false, 'PEAR2_Pyrus_PackageFile_v2_Remote');
        $url['info'] = $pf;
        return $url;
    }

    /**
     * @param array output of package.getDownloadURL
     * @param string|array|object information for detecting packages to be downloaded, and
     *                            for errors
     * @param array name information of the package
     * @access private
     */
    function analyze($info, $param, $pname)
    {
        if (!$info) {
            // no releases exist
            if (!is_string($param)) {
                $saveparam = ", cannot download \"$param\"";
            } else {
                $saveparam = '';
            }
            throw new PEAR2_Pyrus_Package_Exception('No releases for package "' .
                PEAR2_Pyrus_Config::parsedPackageNameToString($pname, true) . '" exist' . $saveparam);
        }
        if (strtolower($info['info']->channel) != strtolower($pname['channel'])) {
            // downloaded package information claims it is from a different channel
            throw new PEAR2_Pyrus_Package_Exception(
                'SECURITY ERROR: package in channel "' . $pname['channel'] .
                '" retrieved another channel\'s name for download! ("' .
                $info['info']->channel . '")');
        }
        if (isset($info['url'])) {
            $this->checkDeprecated($info);
            return $info;
        }
        // package exists, no releases fit the criteria for downloading
        $reg = PEAR2_Pyrus_Config::current()->registry;
        if ($reginfo = $reg->exists($info['info']->package, $info['info']->channel)) {
            // package is already installedy
        }
        $instead =  ', will instead download version ' . $info['version'] .
                    ', stability "' . $info['info']->state . '"';
        // releases exist, but we failed to get any
        if (isset(PEAR2_Pyrus_Installer::$options['force'])) {
            $this->analyzeForced($info, $param, $pname);
        } else {
            if (isset($info['php']) && $info['php']) {
                // package download failed because the package requires a higher PHP
                // version than our own
                throw new PEAR2_Pyrus_Package_Exception('Failed to download ' .
                    $pname['channel'] . '/' . $pname['package'] .
                    ', latest release is version ' . $info['php']['v'] .
                    ', but it requires PHP version "' .
                    $info['php']['m'] . '", use "' .
                    PEAR2_Pyrus_Config::parsedPackageNameToString(
                        array('channel' => $pname['channel'], 'package' => $pname['package'],
                        'version' => $info['php']['v'])) . '" to install');
            }
            // construct helpful error message
            if (isset($pname['version'])) {
                $vs = ', version "' . $pname['version'] . '"';
            } elseif (isset($pname['state'])) {
                $vs = ', stability "' . $pname['state'] . '"';
            } else {
                $vs = ' within preferred state "' . PEAR2_Pyrus_Config::current()->preferred_state . '"';
            }
            // this is only set by the "download-all" command
            if (isset(PEAR2_Pyrus_Installer::$options['ignorepreferred_state'])) {
                throw new PEAR2_Pyrus_Package_Exception(
                    'Failed to download ' . $pname['channel'] . '/' . $pname['package'] . '-'
                     . $vs .
                    ', latest release is version ' . $info['version'] .
                    ', stability "' . $info['info']->stability['release'] . '", use "' .
                    $pname['channel'] . '/' . $pname['package'] . '-' .
                    $info['version'] . '" to install');
            }
            throw new PEAR2_Pyrus_Package_Exception(
                'Failed to download ' . $pname['channel'] . '/' . $pname['package']
                 . $vs .
                ', latest release is version ' . $info['version'] .
                ', stability "' . $info['info']->stability['release'] . '", use "' .
                $pname['channel'] . '/' . $pname['package'] . '-' .
                $info['version'] . '" to install');
        }
        $this->checkDeprecated($info);
        return $info;
    }

    function analyzeForced($info, $param, $pname)
    {
        if (isset($pname['version'])) {
            $vs = ', version "' . $pname['version'] . '"';
        } elseif (isset($pname['state'])) {
            $vs = ', stability "' . $pname['state'] . '"';
        } elseif ($param == 'dependency') {
            if (!in_array($info['info']->stability['release'],
                  PEAR2_Pyrus_Installer::betterStates(
                  PEAR2_Pyrus_Config::current()->preferred_state, true))) {
                $vs = ' within preferred state "' . PEAR2_Pyrus_Config::current()->preferred_state .
                    '"';
            } else {
                $vs = PEAR2_Pyrus_Dependency_Validator::_getExtraString($pname);
                $instead = '';
            }
        } else {
            $vs = ' within preferred state "' . $this->_config->get(
                'preferred_state') . '"';
        }
        if (!isset(PEAR2_Pyrus_Installer::$options['soft'])) {
            PEAR2_Pyrus_Log::log(1, 'WARNING: failed to download ' . $pname['channel'] .
                '/' . $pname['package'] . $vs . $instead);
        }
        // download the latest release
        return $this->getPackageDownloadUrl(
            array('package' => $pname['package'],
                  'channel' => $pname['channel'],
                  'version' => $info['version']));
    }

    function checkDeprecated($pname)
    {
        if (isset($pname['deprecated']) && $pname['deprecated']) {
            // package is deprecated in favor of another
            PEAR2_Pyrus_Log::log(0,
                'WARNING: "' .
                $pname['info']->channel . '/' . $pname['package'] . '-' .
                '" is deprecated in favor of "' .
                    PEAR2_Pyrus_Config::parsedPackageNameToString($pname['deprecated'], true) .
                '"');
        }
    }
}
