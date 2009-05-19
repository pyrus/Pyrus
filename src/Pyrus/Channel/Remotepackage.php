<?php
/**
 * PEAR2_Pyrus_Channel_Remotepackage
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
 * Remote REST iteration handler
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Channel_Remotepackage extends PEAR2_Pyrus_PackageFile_v2 implements ArrayAccess, Iterator
{
    protected $parent;
    protected $rest;
    protected $releaseList;

    function __construct(PEAR2_Pyrus_IChannel $channelinfo, $releases = null)
    {
        $this->parent = $channelinfo;
        $this->rest = new PEAR2_Pyrus_REST;
        $this->releaseList = $releases;
    }

    function offsetGet($var)
    {
        try {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'p/' . strtolower($var) . '/info.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Channel_Exception('package ' . $var . ' does not exist', $e);
        }
        if (is_string($this->releaseList)) {
            if (isset($this->parent->protocols->rest['REST1.0'])) {
                $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                        'r/' . $lowerpackage . '/allreleases.xml');
            } else {
                $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.3']->baseurl .
                                                        'r/' . $lowerpackage . '/allreleases2.xml');
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
            $this->releaseList = $releases;
        }
        $pxml = clone $this;
        $pxml->channel = $info['c'];
        $pxml->name = $info['n'];
        $pxml->license = $info['l'];
        $pxml->summary = $info['s'];
        $pxml->description = $info['d'];
        return $pxml;
    }

    function offsetSet($var, $value)
    {
        throw new PEAR2_Pyrus_Channel_Exception('remote channel info is read-only');
    }

    function offsetUnset($var)
    {
        throw new PEAR2_Pyrus_Channel_Exception('remote channel info is read-only');
    }

    /**
     * This is very expensive, use sparingly if at all
     */
    function offsetExists($var)
    {
        try {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'p/' . strtolower($var) . '/info.xml');
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    function valid()
    {
        return current($this->releaseList);
    }

    function current()
    {
        $info = current($this->releaseList);
        if (!isset($info['m'])) {
            $info['m'] = '5.2.0'; // guess something lower than us
        }
        return array('stability' => $info['s'], 'minimumphp' => $info['m']);
    }

    function key()
    {
        $info = current($this->releaseList);
        return $info['v'];
    }

    function next()
    {
        return next($this->releaseList);
    }

    function rewind()
    {
        if ($this->releaseList) {
            return reset($this->releaseList);
        }
        if (!$this->name) {
            throw new PEAR2_Pyrus_Channel_Exception('Cannot iterate without first choosing a remote package');
        }
        if (isset($this->parent->protocols->rest['REST1.3'])) {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.3']->baseurl .
                                                    'r/' . strtolower($this->name) . '/allreleases2.xml');
        } else {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'r/' . strtolower($this->name) . '/allreleases.xml');
        }
        $this->releaseList = $info['r'];
    }
}