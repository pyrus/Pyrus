<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Mirror_Sqlite3
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
 * Represents a mirror within a Sqlite3 channel registry.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelRegistry_Mirror_Sqlite3 extends PEAR2_Pyrus_ChannelRegistry_Channel_Sqlite3 implements PEAR2_Pyrus_Channel_IMirror
{
    private $_channel;
    private $_parent;

    function __construct(SQLite3 $db, $mirror, PEAR2_Pyrus_IChannel $parent)
    {
        if ($parent->getName() == '__uri') {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('__uri channel cannot have mirrors');
        }

        $this->_channel = $parent->getName();
        parent::__construct($db, $this->_channel);
        $this->mirror = $mirror;
        $this->_parent = $parent;
    }

    function getChannel()
    {
        return $this->_channel;
    }

    function toChannelObject()
    {
        return $parent;
    }

    /**
     * @return string|false
     */
    function getName()
    {
        return $this->mirror;
    }
}