<?php
class PEAR2_Pyrus_Package_Remote extends PEAR2_Pyrus_Package
{
    private $_parent;
    private $_info;
    /**
     * @param string $package path to package file
     */
    function __construct($package, PEAR2_Pyrus_Package $parent)
    {
        $this->_parent = $parent;
        $this->_info = $package;
        if (!is_array($package) &&
              (preg_match('#^(http[s]?|ftp[s]?)://#', $package))) {
            $this->internal = $this->_fromUrl($package);
        } else {
            $this->internal = $this->_fromString($package);
        }
    }

    /**
     * Convert this remote packagefile into a local .tar, .tgz or .phar
     *
     * @return PEAR2_Pyrus_Package_Tar|PEAR2_Pyrus_Package_Tgz|PEAR2_Pyrus_Package_Phar
     */
    function download()
    {
        if ($this->_type === 'url') {
            
        } else {
            
        }
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
            if ($response->code == '200') {
                $name = basename($response->path);
                if (!@file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $response->body);
            }
            // whew, download worked!
            $a = new PEAR2_Pyrus_Package($dir . DIRECTORY_SEPARATOR .$name);
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
            $pname = PEAR2_Pyrus_Config::current()->channelregistry->parsePackageName($param,
                PEAR2_Pyrus_Config::current()->default_channel);
        } catch (Exception $e) {
            if ($e->why !== 'channel') {
                throw new PEAR2_Pyrus_Package_Exception('Cannot process remote package', $e);
            }
            if ($this->_downloader->discover($parsed['channel'])) {
                if (PEAR2_Pyrus_Config::current()->auto_discover) {
                    try {
                        $pname =
                        PEAR2_Pyrus_Config::current()->
                            channelregistry->parsePackageName($param,
                                PEAR2_Pyrus_Config::current()->default_channel);
                    } catch (Exception $e) {
                        if (is_array($param)) {
                            $param =
                              PEAR2_Pyrus_ChannelRegistry::parsedPackageNameToString($param);
                        }
                        throw new PEAR2_Pyrus_Package_Exception(
                            'invalid package name/package file "' . $param . '"', $e);
                    }
                } else {
//                    if (!isset($options['soft'])) {
                        PEAR2_Pyrus_Log::log(0, 'Channel "' . $parsed['channel'] .
                            '" is not initialized, use ' .
                            '"pyrus channel-discover ' . $parsed['channel'] . '" to initialize' .
                            'or pyrus config-set auto_discover 1');
//                    }
                }
            } else {
                throw new PEAR2_Pyrus_Package_Exception(
                    'invalid package name/package file "' . $param . '"', $e);
            }
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
        try {
            $info = $this->_downloader->_getPackageDownloadUrl($pname);
        } catch (Exception $e) {
            return $info;
        }
        $this->_rawpackagefile = $info['raw'];
        $ret = $this->_analyzeDownloadURL($info, $param, $pname);
        if (PEAR::isError($ret)) {
            return $ret;
        }
        if ($ret) {
            $this->_downloadURL = $ret;
            return $this->_valid = (bool) $ret;
        }
    }

    /**
     * @param array output of package.getDownloadURL
     * @param string|array|object information for detecting packages to be downloaded, and
     *                            for errors
     * @param array name information of the package
     * @param array|null packages to be downloaded
     * @param bool is this an optional dependency?
     * @param bool is this any kind of dependency?
     * @access private
     */
    function _analyzeDownloadURL($info, $param, $pname, $params = null, $optional = false,
                                 $isdependency = false)
    {
        if (!is_string($param) && PEAR_Downloader_Package::willDownload($param, $params)) {
            return false;
        }
        if (!$info) {
            if (!is_string($param)) {
                $saveparam = ", cannot download \"$param\"";
            } else {
                $saveparam = '';
            }
            // no releases exist
            return PEAR::raiseError('No releases for package "' .
                $this->_registry->parsedPackageNameToString($pname, true) . '" exist' . $saveparam);
        }
        if (strtolower($info['info']->getChannel()) != strtolower($pname['channel'])) {
            $err = false;
            if ($pname['channel'] == 'pecl.php.net') {
                if ($info['info']->getChannel() != 'pear.php.net') {
                    $err = true;
                }
            } elseif ($info['info']->getChannel() == 'pecl.php.net') {
                if ($pname['channel'] != 'pear.php.net') {
                    $err = true;
                }
            } else {
                $err = true;
            }
            if ($err) {
                return PEAR::raiseError('SECURITY ERROR: package in channel "' . $pname['channel'] .
                    '" retrieved another channel\'s name for download! ("' .
                    $info['info']->getChannel() . '")');
            }
        }
        if (!isset($info['url'])) {
            if ($this->isInstalled($info)) {
                if ($isdependency && version_compare($info['version'],
                      $this->_registry->packageInfo($info['info']->getPackage(),
                            'version', $info['info']->getChannel()), '<=')) {
                    // ignore bogus errors of "failed to download dependency"
                    // if it is already installed and the one that would be
                    // downloaded is older or the same version (Bug #7219)
                    return false;
                }
            }
            $instead =  ', will instead download version ' . $info['version'] .
                        ', stability "' . $info['info']->getState() . '"';
            // releases exist, but we failed to get any
            if (isset($this->_downloader->_options['force'])) {
                if (isset($pname['version'])) {
                    $vs = ', version "' . $pname['version'] . '"';
                } elseif (isset($pname['state'])) {
                    $vs = ', stability "' . $pname['state'] . '"';
                } elseif ($param == 'dependency') {
                    if (!class_exists('PEAR_Common')) {
                        require_once 'PEAR/Common.php';
                    }
                    if (!in_array($info['info']->getState(),
                          PEAR_Common::betterStates($this->_config->get('preferred_state'), true))) {
                        if ($optional) {
                            // don't spit out confusing error message
                            return $this->_downloader->_getPackageDownloadUrl(
                                array('package' => $pname['package'],
                                      'channel' => $pname['channel'],
                                      'version' => $info['version']));
                        }
                        $vs = ' within preferred state "' . $this->_config->get('preferred_state') .
                            '"';
                    } else {
                        if (!class_exists('PEAR_Dependency2')) {
                            require_once 'PEAR/Dependency2.php';
                        }
                        if ($optional) {
                            // don't spit out confusing error message
                            return $this->_downloader->_getPackageDownloadUrl(
                                array('package' => $pname['package'],
                                      'channel' => $pname['channel'],
                                      'version' => $info['version']));
                        }
                        $vs = PEAR_Dependency2::_getExtraString($pname);
                        $instead = '';
                    }
                } else {
                    $vs = ' within preferred state "' . $this->_config->get(
                        'preferred_state') . '"';
                }
                if (!isset($options['soft'])) {
                    $this->_downloader->log(1, 'WARNING: failed to download ' . $pname['channel'] .
                        '/' . $pname['package'] . $vs . $instead);
                }
                // download the latest release
                return $this->_downloader->_getPackageDownloadUrl(
                    array('package' => $pname['package'],
                          'channel' => $pname['channel'],
                          'version' => $info['version']));
            } else {
                if (isset($info['php']) && $info['php']) {
                    $err = PEAR::raiseError('Failed to download ' .
                        $this->_registry->parsedPackageNameToString(
                            array('channel' => $pname['channel'],
                                  'package' => $pname['package']),
                                true) .
                        ', latest release is version ' . $info['php']['v'] .
                        ', but it requires PHP version "' .
                        $info['php']['m'] . '", use "' .
                        $this->_registry->parsedPackageNameToString(
                            array('channel' => $pname['channel'], 'package' => $pname['package'],
                            'version' => $info['php']['v'])) . '" to install',
                            PEAR_DOWNLOADER_PACKAGE_PHPVERSION);
                    return $err;
                }
                // construct helpful error message
                if (isset($pname['version'])) {
                    $vs = ', version "' . $pname['version'] . '"';
                } elseif (isset($pname['state'])) {
                    $vs = ', stability "' . $pname['state'] . '"';
                } elseif ($param == 'dependency') {
                    if (!class_exists('PEAR_Common')) {
                        require_once 'PEAR/Common.php';
                    }
                    if (!in_array($info['info']->getState(),
                          PEAR_Common::betterStates($this->_config->get('preferred_state'), true))) {
                        if ($optional) {
                            // don't spit out confusing error message, and don't die on
                            // optional dep failure!
                            return $this->_downloader->_getPackageDownloadUrl(
                                array('package' => $pname['package'],
                                      'channel' => $pname['channel'],
                                      'version' => $info['version']));
                        }
                        $vs = ' within preferred state "' . $this->_config->get('preferred_state') .
                            '"';
                    } else {
                        if (!class_exists('PEAR_Dependency2')) {
                            require_once 'PEAR/Dependency2.php';
                        }
                        if ($optional) {
                            // don't spit out confusing error message, and don't die on
                            // optional dep failure!
                            return $this->_downloader->_getPackageDownloadUrl(
                                array('package' => $pname['package'],
                                      'channel' => $pname['channel'],
                                      'version' => $info['version']));
                        }
                        $vs = PEAR_Dependency2::_getExtraString($pname);
                    }
                } else {
                    $vs = ' within preferred state "' . $this->_downloader->config->get(
                        'preferred_state') . '"';
                }
                $options = $this->_downloader->getOptions();
                // this is only set by the "download-all" command
                if (isset($options['ignorepreferred_state'])) {
                    $err = PEAR::raiseError(
                        'Failed to download ' . $this->_registry->parsedPackageNameToString(
                            array('channel' => $pname['channel'], 'package' => $pname['package']),
                                true)
                         . $vs .
                        ', latest release is version ' . $info['version'] .
                        ', stability "' . $info['info']->getState() . '", use "' .
                        $this->_registry->parsedPackageNameToString(
                            array('channel' => $pname['channel'], 'package' => $pname['package'],
                            'version' => $info['version'])) . '" to install',
                            PEAR_DOWNLOADER_PACKAGE_STATE);
                    return $err;
                }
                $err = PEAR::raiseError(
                    'Failed to download ' . $this->_registry->parsedPackageNameToString(
                        array('channel' => $pname['channel'], 'package' => $pname['package']),
                            true)
                     . $vs .
                    ', latest release is version ' . $info['version'] .
                    ', stability "' . $info['info']->getState() . '", use "' .
                    $this->_registry->parsedPackageNameToString(
                        array('channel' => $pname['channel'], 'package' => $pname['package'],
                        'version' => $info['version'])) . '" to install');
                return $err;
            }
        }
        if (isset($info['deprecated']) && $info['deprecated']) {
            $this->_downloader->log(0,
                'WARNING: "' . 
                    $this->_registry->parsedPackageNameToString(
                            array('channel' => $info['info']->getChannel(),
                                  'package' => $info['info']->getPackage()), true) .
                '" is deprecated in favor of "' .
                    $this->_registry->parsedPackageNameToString($info['deprecated'], true) .
                '"');
        }
        return $info;
    }
}