<?php
/**
 * \pear2\Pyrus\ChannelRegistryInterface
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
interface ChannelRegistryInterface
{
    public function add(ChannelInterface $channel, $update = false, $lastmodified = false);
    public function update(ChannelInterface $channel);
    public function delete(ChannelInterface $channel);
    public function get($channel, $strict = true);
    public function exists($channel, $strict = true);
    public function parseName($name);
    public function parsedNameToString($name);
    public function listChannels();
}
