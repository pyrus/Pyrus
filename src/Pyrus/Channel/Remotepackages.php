<?php
/**
 * PEAR2_Pyrus_Channel_Remotepackages
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
 * Remote REST iteration handler for package listing
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Channel_Remotepackages implements ArrayAccess, Iterator
{
    protected $parent;
    public $stability = null;
    protected $rest;
    protected $packageList;

    function __construct(PEAR2_Pyrus_IChannel $channelinfo)
    {
        $this->parent = $channelinfo;
        $this->rest = new PEAR2_Pyrus_REST;
    }

    function offsetGet($var)
    {
        if ($var !== 'devel' && $var !== 'alpha' && $var !== 'beta' && $var !== 'stable') {
            throw new PEAR2_Pyrus_Channel_Exception('Invalid stability requested, must be one of ' .
                                                    'devel, alpha, beta, stable');
        }
        $a = clone $this;
        $a->stability = $var;
        return $a;
    }

    function offsetSet($var, $value)
    {
        throw new PEAR2_Pyrus_Channel_Exception('remote channel info is read-only');
    }

    function offsetUnset($var)
    {
        throw new PEAR2_Pyrus_Channel_Exception('remote channel info is read-only');
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
            $releases = $info[1];
        } else {
            $lowerpackage = current($this->packageList);
        }
        $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                'p/' . $lowerpackage . '/info.xml');
        if (isset($releases)) {
            $pxml = new PEAR2_Pyrus_Channel_Remotepackage($this->parent, $releases);
        } else {
            $pxml = new PEAR2_Pyrus_Channel_Remotepackage($this->parent);
        }
        $pxml->channel = $info['c'];
        $pxml->name = $info['n'];
        $pxml->license = $info['l'];
        $pxml->summary = $info['s'];
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
        $this->packageList = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                             'p/packages.xml');
        $this->packageList = $this->packageList['p'];
        if (!is_array($this->packageList)) {
            $this->packageList = array($this->packageList);
        }
        if (isset($this->stability)) {
            // filter the package list for packages of this stability or better
            $ok = PEAR2_Pyrus_Installer::betterStates($this->stability, true);
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