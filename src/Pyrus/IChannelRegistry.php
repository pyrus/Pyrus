<?php
/**
 * \pear2\Pyrus\IChannelRegistry
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
 * Interface for PEAR2 channel registry.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus;
interface IChannelRegistry
{
    public function add(\pear2\Pyrus\IChannel $channel, $update = false, $lastmodified = false);
    public function update(\pear2\Pyrus\IChannel $channel);
    public function delete(\pear2\Pyrus\IChannel $channel);
    public function get($channel, $strict = true);
    public function exists($channel, $strict = true);
    public function parseName($name);
    public function parsedNameToString($name);
    public function listChannels();
}
