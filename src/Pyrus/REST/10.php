<?php
/**
 * PEAR_REST_10
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
 * Implement REST 1.0
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_REST_10
{
    /**
     * @var PEAR2_Pyrus_REST
     */
    protected $rest;
    protected $options;
    function __construct($options = array())
    {
        $this->options = $options;
        $this->rest = new PEAR2_Pyrus_REST($this->options);
    }

    /**
     * Retrieve information about a remote package to be downloaded from a REST server
     *
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
     * @return array|false|PEAR_Error see {@link returnDownloadURL()}
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
            $info = $this->rest->retrieveData($base . 'r/' . strtolower($package) . '/allreleases.xml');
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
        foreach ($info['r'] as $release) {
            if (!isset($this->options['force']) && ($installed &&
                  version_compare($release['v'], $installed, '<'))) {
                continue;
            }
            if (isset($state)) {
                // try our preferred state first
                if ($release['s'] == $state) {
                    $found = true;
                    break;
                }
                // see if there is something newer and more stable
                // bug #7221
                if (in_array($release['s'], PEAR2_Pyrus_Installer::betterStates($state), true)) {
                    $found = true;
                    break;
                }
            } elseif (isset($version)) {
                if ($release['v'] == $version) {
                    $found = true;
                    break;
                }
            } else {
                if (in_array($release['s'], $states)) {
                    $found = true;
                    break;
                }
            }
        }
        return $this->returnDownloadURL($base, $package, $release, $info, $found);
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
        try {
            $info = $this->rest->retrieveData($base . 'r/' . strtolower($package) . '/allreleases.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Package "' . $deppackage['channel'] . '/' . $deppackage['package']
                . '" dependency "' . $channel . '/' . $package . '" has no releases', $e);
        }
        if (!is_array($info) || !isset($info['r'])) {
            return false;
        }
        $exclude = array();
        $min = $max = $recommended = false;
        $min = isset($dependency['min']) ? $dependency['min'] : false;
        $max = isset($dependency['max']) ? $dependency['max'] : false;
        $recommended = isset($dependency['recommended']) ?
            $dependency['recommended'] : false;
        if (isset($dependency['exclude'])) {
            if (!isset($dependency['exclude'][0])) {
                $exclude = array($dependency['exclude']);
            }
        }
        $release = $found = false;
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
                $found = true; // ... then use it
                break;
            }
        }
        return $this->returnDownloadURL($base, $package, $release, $info, $found);
    }

    /**
     * Take raw data and return the array needed for processing a download URL
     *
     * @param string $base REST base uri
     * @param string $package Package name
     * @param array $release an array of format array('v' => version, 's' => state)
     *                       describing the release to download
     * @param array $info list of all releases as defined by allreleases.xml
     * @param bool|null $found determines whether the release was found or this is the next
     *                    best alternative.  If null, then versions were skipped because
     *                    of PHP dependency
     * @return array|PEAR_Error
     * @access private
     */
    protected function returnDownloadURL($base, $package, $release, $info, $found, $phpversion = false)
    {
        if (!$found) {
            $release = $info['r'][0];
        }
        try {
            $pinfo = $this->rest->retrieveCacheFirst($base . 'p/' . strtolower($package) . '/' .
            'info.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Package "' . $package .
                '" does not have REST info xml available', $e);
        }
        try {
            $releaseinfo = $this->rest->retrieveCacheFirst($base . 'r/' . strtolower($package) . '/' .
            $release['v'] . '.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Package "' . $package . '" Version "' . $release['v'] .
                '" does not have REST xml available', $e);
        }
        try {
            $packagexml = $this->rest->retrieveCacheFirst($base . 'r/' . strtolower($package) . '/' .
            'package.' . $release['v'] . '.xml', false, true);
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Package "' . $package . '" Version "' . $release['v'] .
                '" does not have REST dependency information available', $e);
        }
        $allinfo = $this->rest->retrieveData($base . 'r/' . strtolower($package) .
            '/allreleases.xml');
        if (!is_array($allinfo['r']) || !isset($allinfo['r'][0])) {
            $allinfo['r'] = array($allinfo['r']);
        }
        $compatible = false;
        foreach ($allinfo['r'] as $release) {
            if ($release['v'] != $releaseinfo['v']) {
                continue;
            }
            if (!isset($release['co'])) {
                break;
            }
            $compatible = array();
            if (!is_array($release['co']) || !isset($release['co'][0])) {
                $release['co'] = array($release['co']);
            }
            foreach ($release['co'] as $entry) {
                $comp = array();
                $comp['name'] = $entry['p'];
                $comp['channel'] = $entry['c'];
                $comp['min'] = $entry['min'];
                $comp['max'] = $entry['max'];
                if (isset($entry['x']) && !is_array($entry['x'])) {
                    $comp['exclude'] = $entry['x'];
                }
                $compatible[] = $comp;
            }
            if (count($compatible) == 1) {
                $compatible = $compatible[0];
            }
            break;
        }
        if (isset($pinfo['dc']) && isset($pinfo['dp'])) {
            if (is_array($pinfo['dp'])) {
                $deprecated = array('channel' => (string) $pinfo['dc'],
                                    'package' => trim($pinfo['dp']['_content']));
            } else {
                $deprecated = array('channel' => (string) $pinfo['dc'],
                                    'package' => trim($pinfo['dp']));
            }
        } else {
            $deprecated = false;
        }
        if ($found) {
            return
                array('version' => $releaseinfo['v'],
                      'info' => $packagexml,
                      'package' => $releaseinfo['p']['_content'],
                      'stability' => $releaseinfo['st'],
                      'url' => $releaseinfo['g'],
                      'compatible' => $compatible,
                      'deprecated' => $deprecated,
                );
        } else {
            return
                array('version' => $releaseinfo['v'],
                      'package' => $releaseinfo['p']['_content'],
                      'stability' => $releaseinfo['st'],
                      'info' => $packagexml,
                      'compatible' => $compatible,
                      'deprecated' => $deprecated,
                      'php' => $phpversion
                );
        }
    }

    function listPackages($base)
    {
        try {
            $packagelist = $this->rest->retrieveData($base . 'p/packages.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Cannot list packages', $e);
        }
        if (!is_array($packagelist) || !isset($packagelist['p'])) {
            return array();
        }
        if (!is_array($packagelist['p'])) {
            $packagelist['p'] = array($packagelist['p']);
        }
        return $packagelist['p'];
    }

    /**
     * List all categories of a REST server
     *
     * @param string $base base URL of the server
     * @return array of categorynames
     */
    function listCategories($base)
    {
        $categories = array();

        // c/categories.xml does not exist;
        // check for every package its category manually
        // This is SLOOOWWWW : ///
        try {
            $packagelist = $this->rest->retrieveData($base . 'p/packages.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Could not list categories', $e);
        }
        if (!is_array($packagelist) || !isset($packagelist['p'])) {
            $ret = array();
            return $ret;
        }
        if (!is_array($packagelist['p'])) {
            $packagelist['p'] = array($packagelist['p']);
        }

        try {
            foreach ($packagelist['p'] as $package) {
                $inf = $this->rest->retrieveData($base . 'p/' . strtolower($package) . '/info.xml');
                $cat = $inf['ca']['_content'];
                if (!isset($categories[$cat])) {
                    $categories[$cat] = $inf['ca'];
                }
            }
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Could not list categories', $e);
        }
        return array_values($categories);
    }

    /**
     * List a category of a REST server
     *
     * @param string $base base URL of the server
     * @param string $category name of the category
     * @param boolean $info also download full package info
     * @return array of packagenames
     */
    function listCategory($base, $category, $info=false)
    {
        // gives '404 Not Found' error when category doesn't exist
        try {
            $packagelist = $this->rest->retrieveData($base.'c/'.urlencode($category).'/packages.xml');
        } catch (PEAR2_Pyrus_REST_HTTPException $e) {
            throw new PEAR2_Pyrus_REST_Exception('Unknown category ' . $category, $e);
        }
        if (!is_array($packagelist) || !isset($packagelist['p'])) {
            return array();
        }
        if (!is_array($packagelist['p']) ||
            !isset($packagelist['p'][0])) { // only 1 pkg
            $packagelist = array($packagelist['p']);
        } else {
            $packagelist = $packagelist['p'];
        }

        if ($info == true) {
            // get individual package info
            foreach ($packagelist as $i => $packageitem) {
                $url = sprintf('%s'.'r/%s/latest.txt',
                        $base,
                        strtolower($packageitem['_content']));
                try {
                    $version = $this->rest->retrieveData($url);
                } catch (Exception $e) {
                    break; // skipit
                }
                $url = sprintf('%s'.'r/%s/%s.xml',
                        $base,
                        strtolower($packageitem['_content']),
                        $version);
                try {
                    $info = $this->rest->retrieveData($url);
                } catch (Exception $e) {
                    break; // skipit
                }
                $packagelist[$i]['info'] = $info;
            }
        }

        return $packagelist;
    }


    function listAll($base, $dostable, $basic = true, $searchpackage = false, $searchsummary = false)
    {
        try {
            $packagelist = $this->rest->retrieveData($base . 'p/packages.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Could not retrieve package list to list all',
                $e);
        }
        $ret = array();
        if (!is_array($packagelist) || !isset($packagelist['p'])) {
            return $ret;
        }
        if (!is_array($packagelist['p'])) {
            $packagelist['p'] = array($packagelist['p']);
        }

        // only search-packagename = quicksearch !
        if ($searchpackage && (!$searchsummary || empty($searchpackage))) {
            $newpackagelist = array();
            foreach ($packagelist['p'] as $package) {
                if (!empty($searchpackage) && stristr($package, $searchpackage) !== false) {
                    $newpackagelist[] = $package;
                }
            }
            $packagelist['p'] = $newpackagelist;
        }
        foreach ($packagelist['p'] as $progress => $package) {
            if ($basic) { // remote-list command
                try {
                    if ($dostable) {
                        $latest = $this->rest->retrieveData($base . 'r/' . strtolower($package) .
                            '/stable.txt');
                    } else {
                        $latest = $this->rest->retrieveData($base . 'r/' . strtolower($package) .
                            '/latest.txt');
                    }
                } catch (Exception $e) {
                    $latest = false;
                }
                $info = array('stable' => $latest);
            } else { // list-all command
                try {
                    $inf = $this->rest->retrieveData($base . 'p/' . strtolower($package) . '/info.xml');
                } catch (Exception $e) {
                    throw new PEAR2_Pyrus_REST_Exception(
                        'Cannot list all, can\'t get package info for ' . $package, $e);
                }
                if ($searchpackage) {
                    $found = (!empty($searchpackage) && stristr($package, $searchpackage) !== false);
                    if (!$found && !(isset($searchsummary) && !empty($searchsummary)
                        && (stristr($inf['s'], $searchsummary) !== false
                            || stristr($inf['d'], $searchsummary) !== false)))
                    {
                        continue;
                    };
                }
                try {
                    $releases = $this->rest->retrieveData($base . 'r/' . strtolower($package) .
                        '/allreleases.xml');
                } catch (Exception $e) {
                    continue;
                }
                if (!isset($releases['r'][0])) {
                    $releases['r'] = array($releases['r']);
                }
                unset($latest);
                unset($unstable);
                unset($stable);
                unset($state);
                foreach ($releases['r'] as $release) {
                    if (!isset($latest)) {
                        if ($dostable && $release['s'] == 'stable') {
                            $latest = $release['v'];
                            $state = 'stable';
                        }
                        if (!$dostable) {
                            $latest = $release['v'];
                            $state = $release['s'];
                        }
                    }
                    if (!isset($stable) && $release['s'] == 'stable') {
                        $stable = $release['v'];
                        if (!isset($unstable)) {
                            $unstable = $stable;
                        }
                    }
                    if (!isset($unstable) && $release['s'] != 'stable') {
                        $latest = $unstable = $release['v'];
                        $state = $release['s'];
                    }
                    if (isset($latest) && !isset($state)) {
                        $state = $release['s'];
                    }
                    if (isset($latest) && isset($stable) && isset($unstable)) {
                        break;
                    }
                }
                $deps = array();
                if (!isset($unstable)) {
                    $unstable = false;
                    $state = 'stable';
                    if (isset($stable)) {
                        $latest = $unstable = $stable;
                    }
                } else {
                    $latest = $unstable;
                }
                if (!isset($latest)) {
                    $latest = false;
                }
                if ($latest) {
                    try {
                        $d = $this->rest->retrieveCacheFirst($base . 'r/' . strtolower($package) . '/deps.' .
                           $latest . '.txt');
                        $d = unserialize($d);
                        if ($d) {
                            $pf = new PEAR2_Pyrus_PackageFile_v2;
                            $deps = $pf->dependencies;
                        }
                    } catch (Exception $e) {
                        $deps = false;
                    }
                }
                if (!isset($stable)) {
                    $stable = '-n/a-';
                }
                if (!$searchpackage) {
                    $info = array('stable' => $latest, 'summary' => $inf['s'], 'description' =>
                        $inf['d'], 'deps' => $deps, 'category' => $inf['ca']['_content'],
                        'unstable' => $unstable, 'state' => $state);
                } else {
                    $info = array('stable' => $stable, 'summary' => $inf['s'], 'description' =>
                        $inf['d'], 'deps' => $deps, 'category' => $inf['ca']['_content'],
                        'unstable' => $unstable, 'state' => $state);
                }
            }
            $ret[$package] = $info;
        }
        return $ret;
    }

    function listLatestUpgrades($base, $pref_state, $installed, $channel, &$reg)
    {
        try {
            $packagelist = $this->rest->retrieveData($base . 'p/packages.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Could not retrieve latest upgrades', $e);
        }
        $ret = array();
        if (!is_array($packagelist) || !isset($packagelist['p'])) {
            return $ret;
        }
        if (!is_array($packagelist['p'])) {
            $packagelist['p'] = array($packagelist['p']);
        }
        foreach ($packagelist['p'] as $package) {
            if (!isset($installed[strtolower($package)])) {
                continue;
            }
            $inst_version = $reg->packageInfo($package, 'version', $channel);
            $inst_state = $reg->packageInfo($package, 'release_state', $channel);
            try {
                $info = $this->rest->retrieveData($base . 'r/' . strtolower($package) .
                    '/allreleases.xml');
            } catch (Exception $e) {
                continue; // no remote releases
            }
            if (!isset($info['r'])) {
                continue;
            }
            $found = false;
            $release = false;
            if (!is_array($info['r']) || !isset($info['r'][0])) {
                $info['r'] = array($info['r']);
            }
            // $info['r'] is sorted by version number
            foreach ($info['r'] as $release) {
                if ($inst_version && version_compare($release['v'], $inst_version, '<=')) {
                    // not newer than the one installed
                    break;
                }

                // new version > installed version
                if (!$pref_state) {
                    // every state is a good state
                    $found = true;
                    break;
                } else {
                    $new_state = $release['s'];
                    // if new state >= installed state: go
                    if (in_array($new_state, PEAR2_Pyrus_Installer::betterStates($inst_state, true))) {
                        $found = true;
                        break;
                    } else {
                        // only allow to lower the state of package,
                        // if new state >= preferred state: go
                        if (in_array($new_state, PEAR2_Pyrus_Installer::betterStates($pref_state, true))) {
                            $found = true;
                            break;
                        }
                    }
                }
            }
            if (!$found) {
                continue;
            }
            try {
                $relinfo = $this->rest->retrieveCacheFirst($base . 'r/' . strtolower($package) . '/' .
                $release['v'] . '.xml');
            } catch (Exception $e) {
                throw new PEAR2_Pyrus_REST_Exception('Cannot retrieve latest upgrade release' .
                    ' information for package ' . $package, $e);
            }
            $ret[$package] = array(
                    'version' => $release['v'],
                    'state' => $release['s'],
                    'filesize' => $relinfo['f'],
                );
        }
        return $ret;
    }

    function packageInfo($base, $package)
    {
        try {
            $pinfo = $this->rest->retrieveData($base . 'p/' . strtolower($package) . '/info.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_REST_Exception('Unknown package: "' . $package . '"', $e);
        }
        $releases = array();
        try {
            $allreleases = $this->rest->retrieveData($base . 'r/' . strtolower($package) .
                '/allreleases.xml');
            if (!is_array($allreleases['r']) || !isset($allreleases['r'][0])) {
                $allreleases['r'] = array($allreleases['r']);
            }
            $pf = new PEAR2_Pyrus_PackageFile_v2;
            foreach ($allreleases['r'] as $release) {
                try {
                    $ds = $this->rest->retrieveCacheFirst($base . 'r/' . strtolower($package) . '/deps.' .
                        $release['v'] . '.txt');
                } catch (Exception $e) {
                    continue;
                }
                if (!isset($latest)) {
                    $latest = $release['v'];
                }
                $pf->dependencies = unserialize($ds);
                $ds = $pf->dependencies;
                try {
                    $info = $this->rest->retrieveCacheFirst($base . 'r/' . strtolower($package)
                        . '/' . $release['v'] . '.xml');
                } catch (Exception $e) {
                    continue;
                }
                $releases[$release['v']] = array(
                    'doneby' => $info['m'],
                    'license' => $info['l'],
                    'summary' => $info['s'],
                    'description' => $info['d'],
                    'releasedate' => $info['da'],
                    'releasenotes' => $info['n'],
                    'state' => $release['s'],
                    'deps' => $ds ? $ds : array(),
                );
            }
        } catch (Exception $e) {
            $latest = '';
        }
        if (isset($pinfo['dc']) && isset($pinfo['dp'])) {
            if (is_array($pinfo['dp'])) {
                $deprecated = array('channel' => (string) $pinfo['dc'],
                                    'package' => trim($pinfo['dp']['_content']));
            } else {
                $deprecated = array('channel' => (string) $pinfo['dc'],
                                    'package' => trim($pinfo['dp']));
            }
        } else {
            $deprecated = false;
        }
        return array(
            'name' => $pinfo['n'],
            'channel' => $pinfo['c'],
            'category' => $pinfo['ca']['_content'],
            'stable' => $latest,
            'license' => $pinfo['l'],
            'summary' => $pinfo['s'],
            'description' => $pinfo['d'],
            'releases' => $releases,
            'deprecated' => $deprecated,
            );
    }
}
?>