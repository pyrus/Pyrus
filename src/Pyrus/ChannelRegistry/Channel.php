<?php
/**
 * \Pyrus\ChannelRegistry\Channel
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */

/**
 * A class that represents individual channels within a channel registry
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */
namespace Pyrus\ChannelRegistry;
class Channel extends \Pyrus\ChannelFile\v1 implements \Pyrus\ChannelInterface
{
    private $_parent;
    function __construct(\Pyrus\ChannelRegistryInterface $parent, $data)
    {
        if (is_array($data) && !isset($data['channel']) && !isset($data['attribs'])) {
            $data = array_merge(array('attribs' =>  $this->rootAttributes), $data);
        }

        $this->_parent = $parent;
        parent::__construct($data);
    }

    function __get($var)
    {
        return parent::__get($var);
    }

    function __set($var, $value)
    {
        parent::__set($var, $value);
        $this->_parent->update($this);
    }

    function toChannelFile()
    {
        $ret = new \Pyrus\ChannelFile\v1;
        $ret->fromArray($this->getArray());
        return $ret;
    }
}
