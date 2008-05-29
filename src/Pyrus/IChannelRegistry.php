<?php
/**
 * PEAR2_Pyrus_IChannelRegistry
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
interface PEAR2_Pyrus_IChannelRegistry
{
    public function add(PEAR2_Pyrus_IChannel $channel);
    public function update(PEAR2_Pyrus_IChannel $channel);
    public function delete(PEAR2_Pyrus_IChannel $channel);
    public function get($channel, $strict = true);
    public function exists($channel, $strict = true);
    public function parseName($name);
    public function parsedNameToString($name);
    public function listChannels();
    /**
     * Retrieve a list of package objects that depend on this package
     */
    public function getDependentPackages(PEAR2_Pyrus_IPackage $package);
}
