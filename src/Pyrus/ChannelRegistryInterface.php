<?php
/**
 * \Pyrus\ChannelRegistryInterface
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Interface for PEAR2 channel registry.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
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