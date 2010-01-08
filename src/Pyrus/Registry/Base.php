<?php
/**
 * \pear2\Pyrus\Registry\Base
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Base class for a Pyrus Registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\Registry;
abstract class Base implements \pear2\Pyrus\RegistryInterface
{
    protected $packagename;
    protected $packageList = array();

    /**
     * Used by the registry package classes to update info in an installed package
     */
    public function replace(\pear2\Pyrus\PackageFileInterface $info)
    {
        return $this->install($info, true);
    }

    function cloneRegistry(Base $registry)
    {
        try {
            $packageiterator = $registry->package;
            foreach (\pear2\Pyrus\Config::current()->channelregistry->listChannels() as $channel) {
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
