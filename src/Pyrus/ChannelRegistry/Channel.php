<?php
/**
 * \pear2\Pyrus\ChannelRegistry\Channel
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */

/**
 * A class that represents individual channels within a channel registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus/
 */
namespace pear2\Pyrus\ChannelRegistry;
class Channel extends \pear2\Pyrus\ChannelFile\v1 implements \pear2\Pyrus\IChannel
{
    private $_parent;
    function __construct(\pear2\Pyrus\IChannelRegistry $parent, $data)
    {
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
        $ret = new \pear2\Pyrus\ChannelFile\v1;
        $ret->fromArray($this->getArray());
        return $ret;
    }
}
