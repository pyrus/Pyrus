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
        $info = $this->_downloader->_getPackageDownloadUrl($pname);
        if (PEAR::isError($info)) {
            if ($info->getCode() != -976 && $pname['channel'] == 'pear.php.net') {
                // try pecl
                $pname['channel'] = 'pecl.php.net';
                if ($test = $this->_downloader->_getPackageDownloadUrl($pname)) {
                    if (!PEAR::isError($test)) {
                        $info = PEAR::raiseError($info->getMessage() . ' - package ' .
                            PEAR2_Pyrus_ChannelRegistry::parsedPackageNameToString($pname, true) .
                            ' can be installed with "pecl install ' . $pname['package'] .
                            '"');
                    } else {
                        $pname['channel'] = 'pear.php.net';
                    }
                } else {
                    $pname['channel'] = 'pear.php.net';
                }
            }
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
}