<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Channel
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
class PEAR2_Pyrus_ChannelRegistry_Channel extends PEAR2_Pyrus_ChannelFile_v1 implements PEAR2_Pyrus_IChannel
{
    private $_parent;
    function __construct(PEAR2_Pyrus_IChannelRegistry $parent, $data)
    {
        $this->_parent = $parent;
        parent::__construct($data);
    }

    function __get($var)
    {
        if ($var == 'remotepackages') {
            return new PEAR2_Pyrus_Channel_Remotepackages($this);
        } elseif ($var == 'remotepackage') {
            return new PEAR2_Pyrus_Channel_Remotepackage($this, false);
        }
        return parent::__get($var);
    }

    function __set($var, $value)
    {
        parent::__set($var, $value);
        $this->_parent->update($this);
    }

    function toChannelFile()
    {
        $ret = new PEAR2_Pyrus_ChannelFile_v1;
        $ret->fromArray($this->getArray());
        return $ret;
    }
}
