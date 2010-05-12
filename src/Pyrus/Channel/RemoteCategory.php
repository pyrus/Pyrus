<?php
/**
 * \PEAR2\Pyrus\Channel\RemotePackage
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Remote REST iteration handler
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\Channel;
class RemoteCategory implements \ArrayAccess, \Iterator, \Countable
{
    protected $parent;
    protected $category;
    protected $packagesinfo;
    protected $rest;
    protected $minimumStability;

    function __construct(\PEAR2\Pyrus\ChannelFileInterface $channelinfo, $category, $packagesinfo)
    {
        $this->parent = $channelinfo;
        $this->category = $category;
        if (!isset($packagesinfo['pi'])) {
            $packagesinfo['pi'] = array();
        } elseif (!isset($packagesinfo['pi'][0])) {
            $packagesinfo['pi'] = array($packagesinfo['pi']);
        }

        $this->packagesinfo = $packagesinfo['pi'];
        usort($this->packagesinfo, function($a, $b) {
            return strnatcasecmp($a['p']['n'], $b['p']['n']);
        });

        $this->rest = new \PEAR2\Pyrus\REST;
        $this->minimumStability = \PEAR2\Pyrus\Config::current()->preferred_state;
    }

    function __get($var)
    {
        if ($var == 'name') {
            return $this->category;
        }

        if ($var == 'basiclist') {
            $ret = array();
            foreach ($this->packagesinfo as $info) {
                if (!isset($info['a']) || !$info['a']) {
                    $ret[] = array('package' => $info['p']['n'],
                                   'latest' => array('v' => 'n/a', 's' => 'n/a', 'm' => 'n/a'),
                                   'stable' => 'n/a');
                    continue;
                }

                if (!isset($info['a']['r'][0])) {
                    $info['a']['r'] = array($info['a']['r']);
                }

                $inf = array('package' => $info['p']['n'], 'latest' => current($info['a']['r']), 'stable' => 'n/a');
                $inf['latest']['m'] = '5.2.0';
                foreach ($info['a']['r'] as $release) {
                    if ($release['s'] == 'stable') {
                        $inf['stable'] = $release['v'];
                        break;
                    }
                }

                $ret[] = $inf;
            }

            return $ret;
        }
    }

    function offsetGet($var)
    {
        $lowerpackage = strtolower($var);
        foreach ($this->packagesinfo as $package) {
            if (strtolower($package['p']['n']) != $lowerpackage) {
                continue;
            }

            return $this->getPackage($package);
        }

        throw new Exception('Unknown remote package in ' .  $this->category . ' category: "' . $var . '"');
    }

    protected function getPackage($package)
    {
        $releases = array();
        if (isset($package['a']) && $package['a']) {
            $releases = $package['a']['r'];
            if (!isset($releases[0])) {
                $releases = array($releases);
            }

            foreach ($releases as $i => $release) {
                if (!isset($release['m'])) {
                    $releases[$i]['m'] = '5.2.0';
                }
            }
        }

        $pxml = new RemotePackage($this->parent, $releases);
        $pxml->channel = $package['p']['c'];
        $pxml->name = $package['p']['n'];
        $pxml->license = $package['p']['l'];
        $pxml->summary = $package['p']['s'];
        $pxml->description = $package['p']['d'];
        $reg = \PEAR2\Pyrus\Config::current()->registry;
        if ($reg->exists($package['p']['n'], $package['p']['c'])) {
            $pxml->setExplicitState($version = $reg->info($package['p']['n'], $package['p']['c'], 'version'));
            $found = false;
            foreach ($pxml as $remoteversion => $rinfo) {
                if (version_compare($remoteversion, $version, '<=')) {
                    continue;
                }

                if (version_compare($rinfo['minimumphp'], phpversion(), '>')) {
                    continue;
                }

                // found one, so upgrade is possible if dependencies pass
                $found = true;
                break;
            }

            // the installed package version satisfies this dependency, don't do anything
            if ($found) {
                $pxml->setUpgradeable();
            }
        } else {
            $pxml->setExplicitState($this->minimumStability);
        }

        return $pxml;
    }

    function offsetExists($var)
    {
        $lowerpackage = strtolower($var);
        foreach ($this->packagesinfo as $package) {
            if (strtolower($package['p']['n']) != $lowerpackage) {
                continue;
            }

            return true;
        }

        return false;
    }

    function count()
    {
        return count($this->packagesinfo);
    }

    function offsetSet($var, $value)
    {
        throw new Exception('remote channel info is read-only');
    }

    function offsetUnset($var)
    {
        throw new Exception('remote channel info is read-only');
    }

    function valid()
    {
        return current($this->packagesinfo);
    }

    function current()
    {
        return $this->getPackage(current($this->packagesinfo));
    }

    function next()
    {
        return next($this->packagesinfo);
    }

    function rewind()
    {
        return reset($this->packagesinfo);
    }

    function key()
    {
        $current = current($this->packagesinfo);
        return $current['p']['n'];
    }
}