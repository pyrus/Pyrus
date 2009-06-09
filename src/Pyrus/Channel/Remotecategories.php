<?php
/**
 * PEAR2_Pyrus_Channel_Remotecategories
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
 * Remote REST iteration handler for category listing
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Channel_Remotecategories implements ArrayAccess, Iterator
{
    protected $parent;
    public $category = null;
    protected $rest;
    protected $categoryList;

    function __construct(PEAR2_Pyrus_IChannel $channelinfo)
    {
        $this->parent = $channelinfo;
        if (!isset($this->parent->protocols->rest['REST1.1'])) {
            throw new PEAR2_Pyrus_Channel_Exception('Cannot access remote categories without REST1.1 protocol');
        }
        $this->rest = new PEAR2_Pyrus_REST;
    }

    function offsetGet($var)
    {
        $a = clone $this;
        $a->category = $var;
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
        return current($this->categoryList);
    }

    function current()
    {
        $category = $this->key();
        $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.1']->baseurl .
                                                'c/' . urlencode($category) . '/packagesinfo.xml');
        return new PEAR2_Pyrus_Channel_Remotecategory($this->parent, $category, $info);
    }

    function key()
    {
        $cur = current($this->categoryList);
        return urldecode($cur['_content']);
    }

    function next()
    {
        return next($this->categoryList);
    }

    function rewind()
    {
        $this->categoryList = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.1']->baseurl .
                                                             'c/categories.xml');
        $this->categoryList = $this->categoryList['c'];
        if (!isset($this->categoryList[0])) {
            $this->categoryList = array($this->categoryList);
        }
    }
}