<?php
/**
 * Package release management within the SimpleChannelServer.
 *
 * @category Developer
 * @package  PEAR2_SimpleChannelServer
 * @author   Greg Beaver <cellog@php.net>
 * @license  New BSD?
 * @link     http://svn.php.net/viewvc/pear2/sandbox/SimpleChannelServer/
 */
class PEAR2_SimpleChannelServer_REST_Release extends
      PEAR2_SimpleChannelServer_REST_Manager
{
    /**
     * Save a package release's REST information
     *
     * @param \PEAR2\Pyrus\Package $new      new package to be released
     * @param string              $releaser handle of the maintainer who released this package
     *
     * @return void
     */
    function save(\PEAR2\Pyrus\Package $new, $releaser)
    {
        $this->saveInfo($new, $releaser);
        $this->saveInfo2($new, $releaser);
        $this->saveAll($new);
        $this->saveAll2($new);
        $this->saveStability($new);
        $this->savePackageDeps($new);
        $this->savePackageXml($new);
    }

    /**
     * Delete a release from REST information
     *
     * @param \PEAR2\Pyrus\Package $new Package to be erased
     *
     * @return void
     */
    function erase(\PEAR2\Pyrus\Package $new)
    {
        $this->saveAll($new, true);
        $xml = $this->saveAll2($new, true);
        if (!count($xml['a']['r'])) return;
        // reconstruct stability stuff
        foreach ($xml['a']['r'] as $release) {
            if (!isset($latest)) {
                $latest = $release['v'];
            }
            if ($release['s'] == 'stable' && !isset($stable)) {
                $stable = $release['v'];
            }
            if ($release['s'] == 'beta' && !isset($beta)) {
                $beta = $release['v'];
            }
            if ($release['s'] == 'alpha' && !isset($alpha)) {
                $alpha = $release['v'];
            }
        }
        $this->saveReleaseREST(strtolower($new->name) . '/latest.txt', $latest, false);
        if (isset($stable)) {
            $this->saveReleaseREST(strtolower($new->name) . '/stable.txt', $stable, false);
        }
        if (isset($beta)) {
            $this->saveReleaseREST(strtolower($new->name) . '/beta.txt', $beta, false);
        }
        if (isset($alpha)) {
            $this->saveReleaseREST(strtolower($new->name) . '/alpha.txt', $alpha, false);
        }
    }

    /**
     * save rest.release release REST
     *
     * @param \PEAR2\Pyrus\Package $new      package to save info for
     * @param string              $releaser releasing maintainer's handle
     *
     * @return void
     */
    function saveInfo(\PEAR2\Pyrus\Package $new, $releaser)
    {
        $xml            = $this->_getProlog('r', 'release');
        $xml['r']['p']  = array(
                'attribs' => array(
                'xlink:href' =>
                $this->getPackageRESTLink(strtolower($new->name))
            ),
            '_content' => $new->name);
        $xml['r']['c']  = $this->channel;
        $category       = PEAR2_SimpleChannelServer_Categories::getPackageCategory($new->name);
        $xml['r']['v']  = $new->version['release'];
        $xml['r']['st'] = $new->stability['release'];
        $xml['r']['l']  = $new->license['name'];
        $xml['r']['m']  = $releaser;
        $xml['r']['s']  = $new->summary;
        $xml['r']['d']  = $new->description;
        $xml['r']['da'] = $new->date. ' ' . $new->time;
        $xml['r']['n']  = $new->notes;
        $xml['r']['f']  = filesize($new->archivefile);
        $xml['r']['g']  = 'http://' . $this->channel . '/get/' . $new->name .
            '-' . $new->version['release'];
        $xml['r']['x']  = array('attribs' => array(
            'xlink:href' => 'package.' . $new->version['release'] . '.xml'
        ));
        $this->saveReleaseREST(strtolower($new->name) . '/' .
            $new->version['release'] . '.xml', $xml);
    }

    /**
     * save rest.release2 release REST
     *
     * @param \PEAR2\Pyrus\Package $new      package to be saved
     * @param string              $releaser releasing maintainer's handle
     *
     * @return void
     */
    function saveInfo2(\PEAR2\Pyrus\Package $new, $releaser)
    {
        $xml            = $this->_getProlog('r', 'release2');
        $xml['r']['p']  = array(
                'attribs' => array(
                'xlink:href' =>
                $this->getPackageRESTLink(strtolower($new->name))
            ),
            '_content' => $new->name);
        $xml['r']['c']  = $this->channel;
        $category       = PEAR2_SimpleChannelServer_Categories::getPackageCategory($new->name);
        $xml['r']['v']  = $new->version['release'];
        $xml['r']['a']  = $new->version['api'];
        $xml['r']['mp'] = $new->dependencies['required']->php->min;
        $xml['r']['st'] = $new->stability['release'];
        $xml['r']['l']  = $new->license['name'];
        $xml['r']['m']  = $releaser;
        $xml['r']['s']  = $new->summary;
        $xml['r']['d']  = $new->description;
        $xml['r']['da'] = $new->date . ' ' . $new->time;
        $xml['r']['n']  = $new->notes;
        $xml['r']['f']  = filesize($new->archivefile);
        $xml['r']['g']  = 'http://' . $this->channel . '/get/' . $new->name .
            '-' . $new->version['release'];
        $xml['r']['x']  = array('attribs' => array(
            'xlink:href' => 'package.' . $new->version['release'] . '.xml'
        ));
        $this->saveReleaseREST(strtolower($new->name) . '/v2.' .
            $new->version['release'] . '.xml', $xml);
    }

    /**
     * Save a release's package.xml contents
     *
     * @param \PEAR2\Pyrus\Package $new package to be saved
     *
     * @return void
     */
    function savePackageXml(\PEAR2\Pyrus\Package $new)
    {
        $this->saveReleaseREST(strtolower($new->name) . '/package.' .
            $new->version['release'] . '.xml', file_get_contents($new->packagefile),
            false);
    }

    /**
     * Save a serialized representation of a package's dependencies
     *
     * @param \PEAR2\Pyrus\Package $new package to be saved
     *
     * @return void
     */
    function savePackageDeps(\PEAR2\Pyrus\Package $new)
    {
        $this->saveReleaseREST(strtolower($new->name) . '/deps.' .
            $new->version['release'] . '.txt', serialize($new->rawdeps),
            false);
    }

    /**
     * save REST information for all releases of this package
     *
     * @param \PEAR2\Pyrus\Package $new   package to save all release info for
     * @param bool                $erase if true, the release represented by the
     *                                   version of $new will be removed.
     *
     * @return void
     */
    function saveAll(\PEAR2\Pyrus\Package $new, $erase = false, $is2 = false)
    {
        if ($is2) {
            $is2 = '2';
        } else {
            $is2 = '';
        }
        $reader = new \PEAR2\Pyrus\XMLParser;
        $path   = $this->getRESTPath('r', strtolower($new->name) .
            DIRECTORY_SEPARATOR . 'allreleases' . $is2 . '.xml');
        if (file_exists($path)) {
            $xml = $reader->parse($path);
            if (isset($xml['a']['r']) && !isset($xml['a']['r'][0])) {
                $xml['a']['r'] = array($xml['a']['r']);
            }
        } else {
            $xml           = $this->_getProlog('a', 'allreleases' . $is2);
            $xml['a']['p'] = $new->name;
            $xml['a']['c'] = $this->chan;
            $xml['a']['r'] = array();
        }
        if ($erase) {
            foreach ($xml['a']['r'] as $i => $release) {
                if ($release['v'] === $new->version['release']) {
                    unset($xml['a']['r'][$i]);
                    $xml['a']['r'] = array_values($xml['a']['r']);
                    break;
                }
            }
            if (!count($xml['a']['r'])) {
                // no releases, erase all traces
                foreach (new DirectoryIterator($this->getRESTPath('r',
                         strtolower($new->name))) as $name => $info) {
                    if ($info->isDot()) continue;
                    unlink($name);
                }
            }
        } else {
            $info = array(
                'v' => $new->version['release'],
                's' => $new->stability['release'],
            );
            if ($is2) {
                $info['m'] = $new->dependencies['required']->php->min;
            }
            if (count($new->compatible)) {
                $info['co'] = array();
                foreach ($new->compatible as $package=>$cinfo) {
                    if (strpos($package, '/')) {
                        $c = substr($package, 0, strpos($package, '/'));
                        $package = str_replace(array($c, '/'), '', $package);
                    } else {
                        $c = 'pear.php.net';
                    }
                    unset($cinfo['channel']);
                    unset($cinfo['package']);
                    if (isset($cinfo['exclude'])) {
                        $info['x'] = $cinfo['exclude'];
                        unset($cinfo['exclude']);
                    }

                    $info['co'][] = array_merge(array('c' => $c, 'p' => $package), $cinfo);
                }
            }
            $test = $xml['a']['r'];
            if (count($test) && !isset($test[0])) {
                if ($test['v'] != $info['v']) {
                    $test = array($info, $test);
                }
            } else {
                $found = false;
                foreach ($test as $i => $rel) {
                    if ($rel['v'] == $info['v']) {
                        $found = true;
                        $test[$i] = $info;
                        break;
                    }
                }
                if (!$found) {
                    array_unshift($test, $info);
                }
            }
            if (count($test) == 1) {
                $test = $test[0];
            }
            $xml['a']['r'] = $test;
        }
        $this->saveReleaseREST(strtolower($new->name) . '/allreleases' . $is2 . '.xml', $xml);
        return $xml;
    }

    /**
     * save REST information for all releases (version 2) of this package
     *
     * @param \PEAR2\Pyrus\Package $new   package to save all releases for
     * @param bool                $erase if true, the release represented by the
     *                                   version of $new will be removed.
     *
     * @return void
     */
    function saveAll2(\PEAR2\Pyrus\Package $new, $erase = false)
    {
        return $this->saveAll($new, $erase, true);
    }

    /**
     * save REST stability version info in .txt files
     *
     * @param \PEAR2\Pyrus\Package $new package to save stability for
     *
     * @return void
     */
    function saveStability(\PEAR2\Pyrus\Package $new)
    {
        $this->saveReleaseREST(strtolower($new->name) . '/latest.txt',
            $new->version['release'], false);
        $this->saveReleaseREST(strtolower($new->name) . '/' . $new->state . '.txt',
            $new->version['release'], false);
    }
}
