<?php
/**
 * PEAR2_Pyrus_Registry_Base
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
 * Base class for a Pyrus Registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
abstract class PEAR2_Pyrus_Registry_Base implements PEAR2_Pyrus_IRegistry
{
    protected $packagename;
    protected $packageList = array();

    /**
     * Used by the registry package classes to update info in an installed package
     */
    public function replace(PEAR2_Pyrus_IPackageFile $info)
    {
        return $this->install($info, true);
    }

    function cloneRegistry(PEAR2_Pyrus_Registry_Base $registry)
    {
        try {
            $packageiterator = $registry->package;
            foreach (PEAR2_Pyrus_Config::current()->channelregistry->listChannels() as $channel) {
                $packageiterator->setIteratorChannel($channel);
                foreach ($packageiterator as $package) {
                    if ($this->exists($package->name, $package->channel)) {
                        $old = $this->toPackageFile($package->name, $package->channel);
                        if ($old->date == $package->date && $old->time == $package->time) {
                            continue;
                        }
                    }
                    $this->replace($package);
                }
            }
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot clone registry', $e);
        }
    }
}
