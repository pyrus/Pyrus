<?php
/**
 * \pear2\Pyrus\Channel\Remotecategories
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
namespace pear2\Pyrus\Channel;
class Remotecategories implements \ArrayAccess, \Iterator
{
    protected $parent;
    protected $rest;
    protected $categoryList;

    function __construct(\pear2\Pyrus\ChannelInterface $channelinfo)
    {
        $this->parent = $channelinfo;
        if (!isset($this->parent->protocols->rest['REST1.1'])) {
            throw new \pear2\Pyrus\Channel\Exception('Cannot access remote categories without REST1.1 protocol');
        }
        $this->rest = new \pear2\Pyrus\REST;
    }

    function offsetGet($var)
    {
        $info = $this->rest->retrieveCacheFirst($this->parent->protocols->rest['REST1.1']->baseurl .
                                                'c/' . urlencode($var) . '/packagesinfo.xml');
        return new \pear2\Pyrus\Channel\Remotecategory($this->parent, $var, $info);
    }

    function offsetSet($var, $value)
    {
        throw new \pear2\Pyrus\Channel\Exception('remote channel info is read-only');
    }

    function offsetUnset($var)
    {
        throw new \pear2\Pyrus\Channel\Exception('remote channel info is read-only');
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
        return new \pear2\Pyrus\Channel\Remotecategory($this->parent, $category, $info);
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