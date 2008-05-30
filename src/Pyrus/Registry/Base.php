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
abstract class PEAR2_Pyrus_Registry_Base implements ArrayAccess, PEAR2_Pyrus_IRegistry, Iterator
{
    protected $packagename;
    protected $packageList = array();
    function offsetExists($offset)
    {
        $info = PEAR2_Pyrus_Config::parsePackageName($offset);
        if (is_string($info)) {
            return false;
        }
        return $this->exists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
    {
        $info = PEAR2_Pyrus_Config::parsePackageName($offset, true);
        $this->packagename = $offset;
        $ret = clone $this;
        unset($this->packagename);
        return $ret;
    }

    function offsetSet($offset, $value)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot install or upgrade packages, registry is read-only');
        }
        if ($offset == 'upgrade') {
            $this->upgrade($value);
        }
        if ($offset == 'install') {
            $this->install($value);
        }
    }

    function offsetUnset($offset)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_Registry_Exception('Cannot uninstall packages, registry is read-only');
        }
        $info = PEAR2_Pyrus_Config::parsePackageName($offset);
        if (is_string($info)) {
            return;
        }
        $this->uninstall($info['package'], $info['channel']);
    }

    function __get($var)
    {
        if (!isset($this->packagename)) {
            throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
            ' from unknown package');
        }
        $info = PEAR2_Pyrus_Config::parsePackageName($this->_packagename);
        return $this->info($info['package'], $info['channel'], $var);
    }

    function current()
    {
        $packagename = current($this->packageList);
        return $this->package[PEAR2_Pyrus_Config::current()->default_channel . '/' . $packagename];
    }

    function key()
    {
        return key($this->packageList);
    }

    function valid()
    {
        return current($this->packageList);
    }

    function next()
    {
        return next($this->packageList);
    }

    function rewind()
    {
        $this->packageList = $this->listPackages(PEAR2_Pyrus_Config::current()->default_channel);
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
