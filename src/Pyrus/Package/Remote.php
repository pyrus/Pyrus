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
    protected $parsedname;
    protected $explicitState;
    protected $explicitVersion;
    protected $explicitGroup;
    protected $type;
    /**
     * For easy unit testing
     */
    static public $downloadClass = 'PEAR2_HTTP_Request';
    /**
     * @param string $package path to package file
     */
    function __construct($package, PEAR2_Pyrus_Package $parent = null)
    {
        $this->_info = $package;
        if (!is_array($package) &&
              (preg_match('#^(http[s]?|ftp[s]?)://#', $package))) {
            $this->internal = $this->fromUrl($package);
        } else {
            $this->internal = $this->fromString($package);
        }
        $this->from = $parent;
    }

    function isStatic()
    {
        if ($this->type == 'url') {
            return true;
        }
        return $this->explicitVersion;
    }

    /**
     * Convert this remote packagefile into a local .tar, .tgz or .phar
     *
     * @return PEAR2_Pyrus_Package_Base
     */
    function download()
    {
        if ($this->type === 'url') {
            return $this->internal;
        }

        $internal = $this->internal->download();
        $internal->setFrom($this->internal);
        $this->internal = $internal;
        return $this->internal;
    }

    protected function fromUrl($param, $saveparam = '')
    {
        $this->type = 'url';
        $dir = PEAR2_Pyrus_Config::current()->download_dir;
        try {
            $download = static::$downloadClass;
            $http = new $download($param);
            $response = $http->sendRequest();
            $name = 'unknown.tgz';
            if ($response->code != '200') {
                throw new PEAR2_Pyrus_Package_Exception('Download failed, received ' . $response->code);
            }

            if (isset($response->headers['content-disposition'])) {
                if (preg_match('/filename="(.+)"/', $response->headers['content-disposition'], $match)) {
                    $name = $match[1];
                }
            }

            if (!@file_exists($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($dir . DIRECTORY_SEPARATOR . $name, $response->body);

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
    protected function fromString($param)
    {
        try {
            $pname = PEAR2_Pyrus_Config::parsePackageName($param, true);
        } catch (Exception $e) {
            if ($e->why !== 'channel') {
                throw new PEAR2_Pyrus_Package_Exception(
                    'invalid package name/package file "' . $param . '"', $e);
            }

            if (PEAR2_Pyrus_Config::current()->auto_discover) {
                try {
                    try {
                        $chan = new PEAR2_Pyrus_Channel(
                                    new PEAR2_Pyrus_ChannelFile('https://' . $e->params['channel'] . '/channel.xml',
                                                                false, true));
                    } catch (\Exception $e) {
                        $chan = new PEAR2_Pyrus_Channel(
                                    new PEAR2_Pyrus_ChannelFile('http://' . $e->params['channel'] . '/channel.xml',
                                                                false, true));
                    }
                } catch (\Exception $e) {
                    throw new PEAR2_Pyrus_Package_Exception(
                        'Cannot auto-discover channel ' . $e->params['channel'], $e);
                }
                PEAR2_Pyrus_Config::current()->channelregistry[] = $chan;
                try {
                    PEAR2_Pyrus_Config::parsePackageName($param,
                            PEAR2_Pyrus_Config::current()->default_channel);
                } catch (\Exception $e) {
                    throw new PEAR2_Pyrus_Package_Exception(
                        'invalid package name/package file "' . $param . '"', $e);
                }
            } else {
                PEAR2_Pyrus_Log::log(0, 'Channel "' . $param['channel'] .
                    '" is not initialized, use ' .
                    '"pyrus channel-discover ' . $param['channel'] . '" to initialize' .
                    'or pyrus set auto_discover 1');
            }
        }

        $this->parsedname    = $pname;
        $this->explicitVersion = isset($pname['version']) ? $pname['version'] : false;
        $this->explicitState = isset($pname['state']) ? $pname['state'] : false;
        $this->explicitGroup = isset($pname['group']) ? true            : false;

        try {
            $version = PEAR2_Pyrus_Config::current()->registry->info($pname['package'],
                $pname['channel'], 'version');
        } catch (Exception $e) {
            $version = null;
        }
        if (!isset(PEAR2_Pyrus_Installer::$options['force']) &&
              !isset(PEAR2_Pyrus_Installer::$options['downloadonly']) &&
              $version && $this->explicitVersion &&
              !isset($pname['group'])) {
            if (version_compare($version, $pname['version'], '>=')) {
                throw new PEAR2_Pyrus_Package_InstalledException(
                    PEAR2_Pyrus_Config::parsedPackageNameToString($parr, true) .
                    ' is already installed and is newer than detected ' .
                    'release version ' . $pname['version']);
            }
        }

        $this->type = 'abstract';
        $ret = $this->getPackageDownloadUrl($pname);
        if ($this->explicitVersion) {
            $ret->version['release'] = $this->explicitVersion;
        }
        return $ret;
    }

    /**
     * @param array output of {@link parsePackageName()}
     * @access private
     */
    function getPackageDownloadUrl($parr)
    {
        // getDownloadURL returns an array.  On error, it only contains information
        // on the latest release as array(version, info).  On success it contains
        // array(version, info, download url string)
        $state = isset($parr['state']) ? $parr['state'] :  PEAR2_Pyrus_Config::current()->preferred_state;
        if (!isset(PEAR2_Pyrus_Config::current()->channelregistry[$parr['channel']])) {
            throw new PEAR2_Pyrus_Package_Exception(
                'Unknown remote channel: ' . $parr['channel']);
        }

        try {
            $chan = PEAR2_Pyrus_Config::current()->channelregistry[$parr['channel']];
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Package_Exception('Cannot retrieve download information ' .
                'for remote abstract package ' . $parr['channel'] . '/' . $parr['package'], $e);
        }

        $p_mirror = PEAR2_Pyrus_Config::current()->preferred_mirror;
        $mirror   = isset($chan->mirrors[$p_mirror]) ? $chan->mirrors[$p_mirror] : $chan;
        return $mirror->remotepackage[$parr['package']];
    }
}