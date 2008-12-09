<?php
/**
 * PEAR2_Pyrus_Package_Dependency
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
 * Class represents a package dependency.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Package_Dependency extends PEAR2_Pyrus_Package_Remote
{
    protected $subpackage;
    protected $required;
    function __construct(array $dependency, PEAR2_Pyrus_Package $parent, $subpackage = false,
                         $required = false)
    {
        $this->subpackage = $subpackage;
        $this->required = $required;
        $dependency['package'] = $dependency['name'];
        parent::__construct($dependency, $parent);
    }

    function getPackageDownloadUrl($parr)
    {
        $curchannel = PEAR2_Pyrus_Config::current()->default_channel;
        if (isset($parr['uri'])) {
            try {
                $chan = PEAR2_Pyrus_Config::current()->channelregistry['__uri'];
            } catch (Exception $e) {
                throw new PEAR2_Pyrus_Package_Exception('Cannot retrieve download information ' .
                    'for remote abstract package ' . $parr['channel'] . '/' . $parr['package'], $e);
            }

            PEAR2_Pyrus_Config::current()->default_channel = $parr['channel'];
            try {
                $version = PEAR2_Pyrus_Config::current()->registry->info($parr['name'],
                    '__uri', 'version');
            } catch (Exception $e) {
                $version = null;
            }
        } else {
            $remotechannel = $parr['channel'];
            if (!isset(PEAR2_Pyrus_Config::current()->channelregistry[$remotechannel])) {
                do {
                    if (PEAR2_Pyrus_Config::current()->auto_discover) {
                        if (PEAR2_Pyrus_Config::current()->channelregistry->discover($remotechannel)) {
                            break;
                        }
                    }

                    PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
                    throw new PEAR2_Pyrus_Package_Exception(
                        'Unknown remote channel: ' . $remotechannel);
                } while (false);
            }

            try {
                $chan = PEAR2_Pyrus_Config::current()->channelregistry[$remotechannel];
            } catch (Exception $e) {
                PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
                throw new PEAR2_Pyrus_Package_Exception('Cannot retrieve download information ' .
                    'for remote abstract package ' . $remotechannel . '/' . $parr['name'], $e);
            }

            try {
                $version = PEAR2_Pyrus_Config::current()->registry->info($parr['name'],
                    $remotechannel, 'version');
            } catch (Exception $e) {
                $version = null;
            }

            PEAR2_Pyrus_Config::current()->default_channel = $remotechannel;
        }

        $state = isset($parr['state']) ? $parr['state'] : PEAR2_Pyrus_Config::current()
            ->preferred_state;
        if (isset($parr['state']) && isset($parr['version'])) {
            unset($parr['state']);
        }

        if (isset($parr['uri'])) {
            try {
                $info = new PEAR2_Pyrus_Package_Remote($parr['uri'], $this);
            } catch (Exception $e) {
                PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
                throw new PEAR2_Pyrus_Package_Exception('Cannot process URI dependency ' .
                    $parr['uri'], $e);
            }

            return $info;
        }

        $base2 = false;
        $mirrors = $chan->mirrors;
        if (isset($mirrors[PEAR2_Pyrus_Config::current()->preferred_mirror])) {
            $chan = $mirrors[PEAR2_Pyrus_Config::current()->preferred_mirror];
        }

        if (!$chan->supportsREST() ||
              !(($base2 = $chan->getBaseURL('REST1.3')) ||
              ($base = $chan->getBaseURL('REST1.0')))) {
            PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
            throw new PEAR2_Pyrus_Package_Exception('Cannot process dependency ' .
                'information remotely, ' .
                'channel ' . $chan->getName() . ' does not support REST');
        }

        if ($base2) {
            $rest = new PEAR2_Pyrus_REST_13;
            $base = $base2;
        } else {
            $rest = new PEAR2_Pyrus_REST_10;
        }

        try {
            $url = $rest->getDepDownloadURL($base, $parr, $parr,
                $state, $version);
        } catch (Exception $e) {
            PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
            throw new PEAR2_Pyrus_Package_Exception('Cannot process dependency ' .
                $parr['channel'] . '/' . $parr['name'], $e);
        }

        if ($parr['channel'] != $curchannel) {
            PEAR2_Pyrus_Config::current()->default_channel = $curchannel;
        }

        if (!is_string($url['info'])) {
            throw new PEAR2_Pyrus_Package_Exception('Cannot process dependency ' .
                $parr['channel'] . '/' . $parr['name'] .
                ', invalid remote dependencies retrieved from REST - ' .
                'this should never happen');
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
     * @param bool is this an optional dependency?
     * @param bool is this any kind of dependency?
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
        if (isset($reg->package[$info['info']->channel . '/' . $info['info']->name])) {
            // package is already installed
            $reginfo = $reg->package[$info['info']->channel . '/' . $info['info']->name];
            if (version_compare($info['version'],
                  $reginfo->version, '<=')) {
                // ignore bogus errors of "failed to download dependency"
                // if it is already installed and the one that would be
                // downloaded is older or the same version (PEAR Bug #7219)
                return false;
            }
        }

        $instead =  ', will instead download version ' . $info['version'] .
                    ', stability "' . $info['info']->state . '"';
        // releases exist, but we failed to get any
        if (isset(PEAR2_Pyrus_Installer::$options['force'])) {
            $this->analyzeForced($info, $param, $pname);
            $this->checkDeprecated($info);
            return $info;
        }

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
        if (!in_array($info['info']->stability['release'],
              PEAR2_Pyrus_Installer::betterStates(
              PEAR2_Pyrus_Config::current()->preferred_state, true))) {
            if (!$this->required) {
                // don't spit out confusing error message, and don't die on
                // optional dep failure!
                return $this->getPackageDownloadUrl(
                    array('package' => $pname['package'],
                          'channel' => $pname['channel'],
                          'version' => $info['version']));
            }
            $vs = ' within preferred state "' . PEAR2_Pyrus_Config::current()->preferred_state .
                '"';
        } else {
            if (!$this->required) {
                // don't spit out confusing error message, and don't die on
                // optional dep failure!
                return $this->getPackageDownloadUrl(
                    array('package' => $pname['package'],
                          'channel' => $pname['channel'],
                          'version' => $info['version']));
            }
            $vs = PEAR2_Pyrus_Dependency_Validator::_getExtraString($pname);
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

    function analyzeForced($info, $param, $pname)
    {
        if (!in_array($info['info']->stability['release'],
              PEAR2_Pyrus_Installer::betterStates(
              PEAR2_Pyrus_Config::current()->preferred_state, true))) {
            if (!$this->required) {
                // don't spit out confusing error message
                return $this->getPackageDownloadUrl(
                    array('package' => $pname['package'],
                          'channel' => $pname['channel'],
                          'version' => $info['version']));
            }
            $vs = ' within preferred state "' . PEAR2_Pyrus_Config::current()->preferred_state .
                '"';
        } else {
            if (!$this->required) {
                // don't spit out confusing error message
                return $this->getPackageDownloadUrl(
                    array('package' => $pname['package'],
                          'channel' => $pname['channel'],
                          'version' => $info['version']));
            }

            $vs = PEAR2_Pyrus_Dependency_Validator::_getExtraString($pname);
            $instead = '';
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
}