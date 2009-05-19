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

    function __construct(PEAR2_Pyrus_IChannel $channelinfo)
    {
        $this->parent = $channelinfo;
        $this->rest = new PEAR2_Pyrus_REST;
    }

    function offsetGet($var)
    {
        try {
            $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.0']->baseurl .
                                                    'p/' . strtolower($var) . '/info.xml');
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Channel_Exception('package ' . $var . ' does not exist', $e);
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