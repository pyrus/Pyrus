<?php
/**
 * PEAR2_Pyrus_Registry_Package_Base
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
 * Registry package class base
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
abstract class PEAR2_Pyrus_Registry_Package_Base extends PEAR2_Pyrus_PackageFile_v2
                                                 implements ArrayAccess, PEAR2_Pyrus_IPackageFile, Iterator
{

    protected $packagename;
    protected $package;
    protected $channel;
    protected $reg;
    protected $iteratorPackages;
    protected $iteratorChannel;

    function __construct(PEAR2_Pyrus_Registry_Base $cloner)
    {
        $this->reg = $cloner;
    }

    function current()
    {
        $package = current($this->iteratorPackages);
        return $this[$this->iteratorChannel . '/' . $package];
    }

    function key()
    {
        return current($this->iteratorPackages);
    }

    function rewind()
    {
        if (!$this->iteratorChannel) {
            $this->iteratorChannel = PEAR2_Pyrus_Config::current()->default_channel;
        }
        $this->iteratorPackages = $this->reg->listPackages($this->iteratorChannel);
    }

    function next()
    {
        return next($this->iteratorPackages);
    }

    function valid()
    {
        if (!current($this->iteratorPackages)) {
            $this->iteratorChannel = false;
            return false;
        }
        return true;
    }

    function setIteratorChannel($channel)
    {
        $this->iteratorChannel = $channel;
    }

    function fromPackageFile(PEAR2_Pyrus_IPackageFile $package)
    {
        parent::fromPackageFile($package);
        // reconstruct filelist/baseinstalldirs
        // this assumes that the filelist has been flattened, which is
        // always true for registries
        // it also assumes we are not a bundle, which is also always true for
        // registries as bundles are not installable
        $contents = $this->packageInfo['contents']['dir']['file'];
        if (!isset($contents[0])) {
            $contents = array($contents);
        }
        foreach ($contents as $file) {
            $this->filelist[$file['attribs']['name']] = $file;
        }
        if (isset($this->packageInfo['contents']['dir']['attribs']['baseinstalldir'])) {
            $this->baseinstalldirs = array('/' => $this->packageInfo['contents']['dir']['attribs']['baseinstalldir']);
        }
    }

    function offsetExists($offset)
    {
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
        return $this->reg->exists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
    {
        $this->packagename = $offset;
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($this->packagename);
        $this->package = $info['package'];
        $this->channel = $info['channel'];
        $intermediate = $this->reg->toPackageFile($info['package'], $info['channel']);
        $this->fromPackageFile($intermediate);
        $ret = clone $this;
        $this->packagename = null;
        $this->package = null;
        $this->channel = null;
        return $ret;
    }

    function offsetSet($offset, $value)
    {
        $this->reg->install($value);
    }

    function offsetUnset($offset)
    {
        $info = PEAR2_Pyrus_Config::current()->channelregistry->parseName($offset);
        $this->reg->uninstall($info['package'], $info['channel']);
    }

    function toRaw()
    {
        $info = new PEAR2_Pyrus_PackageFile_v2;
        $info->fromArray(array('package' => $this->packageInfo));
        return $info;
    }

    function __get($var)
    {
        if (!isset($this->packagename)) {
            throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        return parent::__get($var);
    }

    function __set($var, $value)
    {
        if (!isset($this->packagename)) {
            throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        parent::__set($var, $value);
        $this->reg->replace($this);
    }

    function getSchemaOK()
    {
        return true;
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
        foreach (array('required', 'optional') as $required) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($this->dependencies[$required]->$package as $d) {
                    if ($d->conflicts) {
                        continue;
                    }
                    if (isset($packages[$d->channel . '/' . $d->name])) {
                        $graph->connect($this, $packages[$d->channel . '/' . $d->name]);
                    }
                }
            }
        }
        foreach ($this->dependencies['group'] as $group) {
            foreach (array('package', 'subpackage') as $package) {
                foreach ($group->$package as $d) {
                    if (isset($packages[$d->channel . '/' . $d->name])) {
                        $graph->connect($this, $packages[$d->channel . '/' . $d->name]);
                    }
                }
            }
        }
    }

    public function validateUninstallDependencies(array $uninstallPackages,
                                                  PEAR2_MultiErrors $errs)
    {
        foreach ($uninstallPackages as $package) {
            $dep = new PEAR2_Pyrus_Dependency_Validator($this->packagename,
                PEAR2_Pyrus_Validate::UNINSTALLING, $errs);
            foreach ($this->getDependentPackages($package) as $deppackage) {
                foreach (array('package', 'subpackage') as $packaged) {
                    foreach ($deppackage->dependencies['required']->$packaged as $d) {
                        if ($package->package == '__uri') {
                            if ($d->name != $package->name || $d->uri != $package->uri) {
                                continue;
                            }
                        } else {
                            if ($d->name != $package->name || $d->channel != $package->channel) {
                                continue;
                            }
                        }
                        $dep->validatePackageUninstall($d, true, $package, $uninstallPackages);
                    }
                    foreach ($deppackage->dependencies['optional']->$packaged as $d) {
                        if ($package->package == '__uri') {
                            if ($d->name != $package->name || $d->uri != $package->uri) {
                                continue;
                            }
                        } else {
                            if ($d->name != $package->name || $d->channel != $package->channel) {
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