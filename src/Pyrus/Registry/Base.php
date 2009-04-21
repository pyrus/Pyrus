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
        $saveChan = PEAR2_Pyrus_Config::current()->default_channel;
        try {
            foreach (PEAR2_Pyrus_Config::current()->channelregistry->listChannels() as $channel) {
                $registry->default_channel = $channel;
                foreach ($registry as $package) {
                    $this->install($package);
                }
            }
        } catch (Exception $e) {
            PEAR2_Pyrus_Config::current()->default_channel = $saveChan;
            throw new PEAR2_Pyrus_Registry_Exception('Cannot clone registry', $e);
        }
        PEAR2_Pyrus_Config::current()->default_channel = $saveChan;
    }

    /**
     * Create vertices/edges of a directed graph for dependencies of this package
     *
     * Iterate over dependencies and create edges from this package to those it
     * depends upon
     * @param PEAR2_Pyrus_DirectedGraph $graph
     * @param array $packages channel/package indexed array of PEAR2_Pyrus_Package objects
     */
    function makeUninstallConnections(PEAR2_Pyrus_DirectedGraph $graph, array $packages)
    {
        $graph->add($this);
        $cxml = $this->toPackageFile($this->name, $this->channel);
        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($cxml->dependencies->$required->$package as $d) {
                    if (isset($d['conflicts'])) {
                        continue;
                    }
                    $dchannel = isset($d['channel']) ? $d['channel'] : '__uri';
                    if (isset($packages[$dchannel . '/' . $d['name']])) {
                        $graph->connect($this, $packages[$dchannel . '/' . $d['name']]);
                    }
                }
            }
        }
        foreach ($cxml->dependencies->group as $group) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($group->$package as $d) {
                    if (isset($d['conflicts'])) {
                        continue;
                    }
                    $dchannel = isset($d['channel']) ? $d['channel'] : '__uri';
                    if (isset($packages[$dchannel . '/' . $d['name']])) {
                        $graph->connect($this, $packages[$dchannel . '/' . $d['name']]);
                    }
                }
            }
        }
    }

    public function validateUninstallDependencies(array $uninstallPackages,
                                                  PEAR2_Multierrors $errs)
    {
        foreach ($uninstallPackages as $package) {
            $dep = new PEAR2_Pyrus_Dependency_Validator($this->name,
                PEAR2_Pyrus_Validate::UNINSTALLING, $errs);
            foreach ($this->getDependentPackages($package) as $deppackage) {
                foreach (array('package', 'subpackage') as $packaged) {
                    foreach ($deppackage->dependencies->required->$packaged as $d) {
                        if ($package->package == '__uri') {
                            if ($d['name'] != $package->name || $d['uri'] != $package->uri) {
                                continue;
                            }
                        } else {
                            if ($d['name'] != $package->name || $d['channel'] != $package->channel) {
                                continue;
                            }
                        }
                        $dep->validatePackageUninstall($d, true, $package, $uninstallPackages);
                    }
                    foreach ($deppackage->dependencies->optional->$packaged as $d) {
                        if ($package->package == '__uri') {
                            if ($d['name'] != $package->name || $d['uri'] != $package->uri) {
                                continue;
                            }
                        } else {
                            if ($d['name'] != $package->name || $d['channel'] != $package->channel) {
                                continue;
                            }
                        }
                        $dep->validatePackageUninstall($d, false, $package, $uninstallPackages);
                    }
                }
            }
        }
    }
}
