<?php
/**
 * \Pyrus\Registry\Base
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

namespace Pyrus\Registry;

/**
 * Base class for a Pyrus Registry
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
abstract class Base implements \Pyrus\RegistryInterface
{
    protected $packagename;
    protected $packageList = array();

    /**
     * Used by the registry package classes to update info in an installed package
     */
    public function replace(\Pyrus\PackageFileInterface $info)
    {
        return $this->install($info, true);
    }

    function cloneRegistry(Base $registry)
    {
        try {
            $packageiterator = $registry->package;
            foreach (\Pyrus\Config::current()->channelregistry->listChannels() as $channel) {
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
        } catch (\Exception $e) {
            throw new Exception('Cannot clone registry', $e);
        }
    }
}
