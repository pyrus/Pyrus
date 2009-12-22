<?php
/**
 * \pear2\Pyrus\ChannelRegistry\Mirror\Sqlite3
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
namespace pear2\Pyrus\ChannelRegistry\Mirror;
class Sqlite3 extends \pear2\Pyrus\ChannelRegistry\Channel\Sqlite3
    implements \pear2\Pyrus\Channel\IMirror
{
    private $_channel;
    private $_parent;

    function __construct(SQLite3 $db, $mirror, \pear2\Pyrus\ChannelInterface $parent)
    {
        if ($parent->name == '__uri') {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('__uri channel cannot have mirrors');
        }

        $this->_channel = $parent->name;
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