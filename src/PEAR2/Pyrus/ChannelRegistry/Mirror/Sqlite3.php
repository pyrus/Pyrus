<?php
/**
 * \Pyrus\ChannelRegistry\Mirror\Sqlite3
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Represents a mirror within a Sqlite3 channel registry.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\ChannelRegistry\Mirror;
class Sqlite3 extends \Pyrus\ChannelRegistry\Sqlite3
    implements \Pyrus\Channel\MirrorInterface
{
    private $_channel;
    private $_parent;

    function __construct(SQLite3 $db, $mirror, \Pyrus\ChannelInterface $parent)
    {
        if ($parent->name == '__uri') {
            throw new \Pyrus\ChannelRegistry\Exception('__uri channel cannot have mirrors');
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