<?php
/**
 * PEAR2_Pyrus_Registry_Channel
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
 * Channels within the registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Channel extends PEAR2_Pyrus_Registry implements ArrayAccess
{
    private $_channelname;
    function __construct(PEAR2_Pyrus_Registry_Sqlite $cloner)
    {
        parent::__construct($cloner->getDatabase());
    }

    function offsetExists($offset)
    {
        if ($offset[0] == '#') {
            return $this->sqlite->channelExists(substr($offset, 1), false);
        }
        return $this->sqlite->channelExists($offset);
    }

    function offsetGet($offset)
    {
        $this->_channelname = $offset;
        $ret = clone $this;
        return $ret;
    }

    function offsetSet($offset, $value)
    {
        if ($offset == 'update') {
            $this->updateChannel($value);
        }
        if ($offset == 'add') {
            $this->addChannel($value);
        }
    }

    function offsetUnset($offset)
    {
        $this->deleteChannel($offset);
    }
}