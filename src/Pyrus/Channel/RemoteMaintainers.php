<?php
/**
 * \PEAR2\Pyrus\Channel\RemoteMaintainers
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Remote REST iteration handler for maitainer listing
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Brett Bieber <saltybeagle@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\Channel;
class RemoteMaintainers implements \ArrayAccess, \Iterator
{
    protected $parent;
    protected $rest;
    protected $maintainerList;

    function __construct(\PEAR2\Pyrus\ChannelFileInterface $channelinfo)
    {
        $this->parent = $channelinfo;
        if (!isset($this->parent->protocols->rest['REST1.1'])) {
            throw new Exception('Cannot access remote categories without REST1.1 protocol');
        }

        $this->rest = new \PEAR2\Pyrus\REST;
        $this->rewind();
    }

    function offsetGet($var)
    {
        $url = $this->parent->protocols->rest['REST1.1']->baseurl . 'm/' . urlencode($var) . '/info.xml';
        $info = $this->rest->retrieveCacheFirst($url);
        return $info;
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
        foreach ($this->maintainerList as $maintainer) {
            if ($maintainer['_content'] == $var) {
                return true;
            }
        }
        return false;
    }

    function valid()
    {
        return current($this->maintainerList);
    }

    function current()
    {
        $handle = $this->key();
        $url = $this->parent->protocols->rest['REST1.1']->baseurl . 'm/' . urlencode($handle) . '/info.xml';
        $info = $this->rest->retrieveCacheFirst($url);
        return $info['m'];
    }

    function key()
    {
        $cur = current($this->maintainerList);
        return urldecode($cur['_content']);
    }

    function next()
    {
        return next($this->maintainerList);
    }

    function rewind()
    {
        $url = $this->parent->protocols->rest['REST1.1']->baseurl . 'm/allmaintainers.xml';
        $this->maintainerList = $this->rest->retrieveCacheFirst($url);
        $this->maintainerList = $this->maintainerList['h'];
        if (!isset($this->maintainerList[0])) {
            $this->maintainerList = array($this->maintainerList);
        }
    }
}