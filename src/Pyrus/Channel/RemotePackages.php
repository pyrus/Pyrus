<?php
/**
 * \Pyrus\Channel\RemotePackages
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
 * Remote REST iteration handler for package listing
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Channel;
class RemotePackages implements \ArrayAccess, \Iterator
{
    protected $parent;
    public $stability = null;
    protected $rest;

    /**
     * The list of packages, usually from p/packages.xml unless a stability
     * flag is set and the list is filtered.
     * 
     * @var array
     */
    protected $packageList;

    function __construct(\Pyrus\ChannelInterface $channelinfo)
    {
        $this->parent = $channelinfo;
        if (!isset($this->parent->protocols->rest['REST1.0'])) {
            throw new Exception('Cannot access remote packages without REST1.0 protocol');
        }

        $this->rest = new \Pyrus\REST;
    }

    function offsetGet($var)
    {
        if ($var !== 'devel' && $var !== 'alpha' && $var !== 'beta' && $var !== 'stable') {
            throw new Exception('Invalid stability ' . $var . ' requested, must be one of ' .
                                                    'devel, alpha, beta, stable');
        }

        $a = clone $this;
        $a->stability = $var;
        return $a;
    }

    function offsetSet($var, $value)
    {
        throw new Exception('remote channel info is read-only');
    }

    function offsetUnset($var)
    {
        throw new Exception('remote channel info is read-only');
    }

    function offsetExists($var)
    {
        // implement this
    }

    function valid()
    {
        return current($this->packageList);
    }

    function current()
    {
        if ($this->stability) {
            $info = current($this->packageList);

            $lowerpackage = $info[0];
            $releases     = $info[1];
        } else {
            $lowerpackage = current($this->packageList);
        }

        // force lowercase because there are some stupid channels out there
        $lowerpackage = strtolower($lowerpackage);

        $url = $this->parent->protocols->rest['REST1.0']->baseurl . 'p/' . $lowerpackage . '/info.xml';
        $info = $this->rest->retrieveCacheFirst($url);

        if (isset($releases)) {
            $pxml = new RemotePackage($this->parent, $releases);
        } else {
            $pxml = new RemotePackage($this->parent);
        }

        $pxml->channel     = $info['c'];
        $pxml->name        = $info['n'];
        $pxml->license     = $info['l'];
        $pxml->summary     = $info['s'];
        $pxml->description = $info['d'];
        return $pxml;
    }

    function getPackage($package)
    {
        $lowerpackage = strtolower($package);
        if (isset($this->parent->protocols->rest['REST1.3'])) {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.3']->baseurl .
                                                    'r/' . $lowerpackage . '/allreleases2.xml');
        } else {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'r/' . $lowerpackage . '/allreleases.xml');
        }

        if (!isset($info['r'][0])) {
            $info['r'] = array($info['r']);
        }

        // filter the package list for packages of this stability or better
        $ok = \Pyrus\Installer::betterStates($this->stability, true);
        $releases = array();
        foreach ($info['r'] as $release) {
            if ($this->stability) {
                if (!in_array($release['s'], $ok)) {
                    continue;
                }
            }

            if (!isset($release['m'])) {
                $release['m'] = '5.2.0';
            }
            $releases[] = $release;

        }

        $url = $this->parent->protocols->rest['REST1.0']->baseurl . 'p/' . $lowerpackage . '/info.xml';
        $info = $this->rest->retrieveCacheFirst($url);

        $pxml = new RemotePackage($this->parent, $releases);

        $pxml->channel     = $info['c'];
        $pxml->name        = $info['n'];
        $pxml->license     = $info['l'];
        $pxml->summary     = $info['s'];
        $pxml->description = $info['d'];
        return $pxml;
    }

    function key()
    {
        return key($this->packageList);
    }

    function next()
    {
        return next($this->packageList);
    }

    function rewind()
    {
        $url = $this->parent->protocols->rest['REST1.0']->baseurl . 'p/packages.xml';
        $this->packageList = $this->rest->retrieveCacheFirst($url);
        $this->packageList = $this->packageList['p'];
        if (!is_array($this->packageList)) {
            $this->packageList = array($this->packageList);
        }

        if (isset($this->stability)) {
            // filter the package list for packages of this stability or better
            $ok = \Pyrus\Installer::betterStates($this->stability, true);
            $filtered = array();
            foreach ($this->packageList as $lowerpackage) {
                if (isset($this->parent->protocols->rest['REST1.3'])) {
                    $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.3']->baseurl .
                                                            'r/' . $lowerpackage . '/allreleases2.xml');
                } else {
                    $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                            'r/' . $lowerpackage . '/allreleases.xml');
                }

                if (!isset($info['r'][0])) {
                    $info['r'] = array($info['r']);
                }

                $releases = array();
                foreach ($info['r'] as $release) {
                    if (!in_array($release['s'], $ok)) {
                        continue;
                    }

                    if (!isset($release['m'])) {
                        $release['m'] = '5.2.0';
                    }

                    $releases[] = $release;
                }

                if (!count($releases)) {
                    continue;
                }

                $filtered[] = array($lowerpackage, $releases);
            }

            $this->packageList = $filtered;
        }
    }
}