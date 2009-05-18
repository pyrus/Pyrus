<?php
/**
 * PEAR_REST_13
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
 * Implement REST 1.3
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_REST_13 extends PEAR2_Pyrus_REST_10
{
    /**
     * Retrieve information about a remote package to be downloaded from a REST server
     *
     * This is smart enough to resolve the minimum PHP version dependency prior to download
     * @param string $base The uri to prepend to all REST calls
     * @param array $packageinfo an array of format:
     * <pre>
     *  array(
     *   'package' => 'packagename',
     *   'channel' => 'channelname',
     *  ['state' => 'alpha' (or valid state),]
     *  -or-
     *  ['version' => '1.whatever']
     * </pre>
     * @param string $prefstate Current preferred_state config variable value
     * @param bool $installed the installed version of this package to compare against
     * @return array|false|PEAR_Error see {@link returnDownloadURL(()}
     */
    function getDownloadURL($base, $packageinfo, $prefstate, $installed)
    {
        $channel = $packageinfo['channel'];
        $package = $packageinfo['package'];
        $states = PEAR2_Pyrus_Installer::betterStates($prefstate, true);
        if (!$states) {
            throw new PEAR2_Pyrus_REST_Exception('"' . $prefstate . '" is not a valid state');
        }
        $state   = isset($packageinfo['state'])   ? $packageinfo['state']   : null;
        $version = isset($packageinfo['version']) ? $packageinfo['version'] : null;
        try {
            $info = $this->rest->retrieveData($base . 'r/' . strtolower($package) . '/allreleases2.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('No releases available for package "' .
                $channel . '/' . $package . '"', $e);
        }
        if (!isset($info['r'])) {
            return false;
        }
        $release = $found = false;
        if (!is_array($info['r']) || !isset($info['r'][0])) {
            $info['r'] = array($info['r']);
        }
        $skippedphp = false;
        foreach ($info['r'] as $release) {
            if (!isset($this->options['force']) && ($installed &&
                  version_compare($release['v'], $installed, '<'))) {
                continue;
            }
            if (isset($state)) {
                // try our preferred state first
                if ($release['s'] == $state) {
                    if (!isset($version) && version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }
                // see if there is something newer and more stable
                // bug #7221
                if (in_array($release['s'], PEAR2_Pyrus_Installer::betterStates($state), true)) {
                    if (!isset($version) && version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }
            } elseif (isset($version)) {
                if ($release['v'] == $version) {
                    if (!isset($this->options['force']) &&
                          !isset($version) &&
                          version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }
            } else {
                if (in_array($release['s'], $states)) {
                    if (version_compare($release['m'], phpversion(), '>')) {
                        // skip releases that require a PHP version newer than our PHP version
                        $skippedphp = $release;
                        continue;
                    }
                    $found = true;
                    break;
                }
            }
        }
        if (!$found && $skippedphp) {
            $found = null;
        }
        return $this->returnDownloadURL($base, $package, $release, $info, $found, $skippedphp);
    }

    function getDepDownloadURL($base, $dependency, $deppackage,
                               $prefstate = 'stable', $installed = false)
    {
        $channel = $dependency['channel'];
        $package = $dependency['name'];
        $states = PEAR2_Pyrus_Installer::betterStates($prefstate, true);
        if (!$states) {
            throw new PEAR2_Pyrus_REST_Exception('"' . $prefstate . '" is not a valid state');
        }
        $state   = isset($dependency['state'])   ? $dependency['state']   : null;
        $version = isset($dependency['version']) ? $dependency['version'] : null;
        // FIXME aren't we just doing the same thing twice over here ?
        $info = $this->rest->retrieveData($base . 'r/' . strtolower($package) .  '/allreleases2.xml');
        try {
            $info = $this->rest->retrieveData($base . 'r/' . strtolower($package) . '/allreleases2.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Package "' . $deppackage['channel'] . '/' . $deppackage['package']
                . '" dependency "' . $channel . '/' . $package . '" has no releases', $e);
        }
        if (!is_array($info) || !isset($info['r'])) {
            return false;
        }
        $exclude = array();
        $min = $max = $recommended = false;
        $pinfo['package'] = $dependency['name'];
        $min = isset($dependency['min']) ? $dependency['min'] : false;
        $max = isset($dependency['max']) ? $dependency['max'] : false;
        $recommended = isset($dependency['recommended']) ? $dependency['recommended'] : false;
        if (isset($dependency['exclude'])) {
            if (!isset($dependency['exclude'][0])) {
                $exclude = array($dependency['exclude']);
            }
        }

        $skippedphp = $release = $found = false;
        if (!is_array($info['r']) || !isset($info['r'][0])) {
            $info['r'] = array($info['r']);
        }
        foreach ($info['r'] as $release) {
            if (!isset($this->options['force']) && ($installed &&
                  version_compare($release['v'], $installed, '<'))) {
                continue;
            }
            if (in_array($release['v'], $exclude)) { // skip excluded versions
                continue;
            }
            // allow newer releases to say "I'm OK with the dependent package"
            if (isset($release['co'])) {
                if (!is_array($release['co']) || !isset($release['co'][0])) {
                    $release['co'] = array($release['co']);
                }
                foreach ($release['co'] as $entry) {
                    if (isset($entry['x']) && !is_array($entry['x'])) {
                        $entry['x'] = array($entry['x']);
                    } elseif (!isset($entry['x'])) {
                        $entry['x'] = array();
                    }
                    if ($entry['c'] == $deppackage['channel'] &&
                          strtolower($entry['p']) == strtolower($deppackage['package']) &&
                          version_compare($deppackage['version'], $entry['min'], '>=') &&
                          version_compare($deppackage['version'], $entry['max'], '<=') &&
                          !in_array($release['v'], $entry['x'])) {
                        if (version_compare($release['m'], phpversion(), '>')) {
                            // skip dependency releases that require a PHP version
                            // newer than our PHP version
                            $skippedphp = $release;
                            continue;
                        }
                        $recommended = $release['v'];
                        break;
                    }
                }
            }
            if ($recommended) {
                if ($release['v'] != $recommended) { // if we want a specific
                    // version, then skip all others
                    continue;
                } else {
                    if (!in_array($release['s'], $states)) {
                        // the stability is too low, but we must return the
                        // recommended version if possible
                        return $this->returnDownloadURL($base, $package, $release, $info, true);
                    }
                }
            }
            if ($min && version_compare($release['v'], $min, 'lt')) { // skip too old versions
                continue;
            }
            if ($max && version_compare($release['v'], $max, 'gt')) { // skip too new versions
                continue;
            }
            if ($installed && version_compare($release['v'], $installed, '<')) {
                continue;
            }
            if (in_array($release['s'], $states)) { // if in the preferred state...
                if (version_compare($release['m'], phpversion(), '>')) {
                    // skip dependency releases that require a PHP version
                    // newer than our PHP version
                    $skippedphp = $release;
                    continue;
                }
                $found = true; // ... then use it
                break;
            }
        }
        if (!$found && $skippedphp) {
            $found = null;
        }
        return $this->returnDownloadURL($base, $package, $release, $info, $found, $skippedphp);
    }
}