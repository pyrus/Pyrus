<?php
/**
 * \pear2\Pyrus\Registry\Package\Base
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
namespace pear2\Pyrus\Registry\Package;
abstract class Base extends \pear2\Pyrus\PackageFile\v2
                                                 implements \ArrayAccess, \pear2\Pyrus\IPackageFile, \Iterator
{

    protected $packagename;
    protected $package;
    protected $channel;
    protected $reg;
    protected $iteratorPackages;
    protected $iteratorChannel;

    function __construct(\pear2\Pyrus\Registry\Base $cloner)
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
            $this->iteratorChannel = \pear2\Pyrus\Config::current()->default_channel;
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

    function fromPackageFile(\pear2\Pyrus\IPackageFile $package)
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
        $info = \pear2\Pyrus\Config::current()->channelregistry->parseName($offset);
        return $this->reg->exists($info['package'], $info['channel']);
    }

    function offsetGet($offset)
    {
        $this->packagename = $offset;
        $info = \pear2\Pyrus\Config::current()->channelregistry->parseName($this->packagename);
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
        $info = \pear2\Pyrus\Config::current()->channelregistry->parseName($offset);
        $this->reg->uninstall($info['package'], $info['channel']);
    }

    function toRaw()
    {
        $info = new \pear2\Pyrus\PackageFile\v2;
        $info->fromArray(array('package' => $this->packageInfo));
        return $info;
    }

    function __get($var)
    {
        if (!isset($this->packagename)) {
            throw new \pear2\Pyrus\Registry\Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        return parent::__get($var);
    }

    function __set($var, $value)
    {
        if (!isset($this->packagename)) {
            throw new \pear2\Pyrus\Registry\Exception('Attempt to retrieve ' . $var .
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
     * @param \pear2\Pyrus\DirectedGraph $graph
     * @param array $packages channel/package indexed array of \pear2\Pyrus\Package objects
     */
    function makeUninstallConnections(\pear2\Pyrus\DirectedGraph $graph, array $packages)
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
                                                  \pear2\MultiErrors $errs)
    {
        $ret = true;
        foreach ($uninstallPackages as $package) {
            foreach ($this->reg->getDependentPackages($package) as $deppackage) {
                $dep = new \pear2\Pyrus\Dependency\Validator(
                    array('channel' => $deppackage->channel, 'package' => $deppackage->name),
                    \pear2\Pyrus\Validate::UNINSTALLING, $errs);
                foreach ($uninstallPackages as $test) {
                    if ($deppackage->isEqual($test)) {
                        // we are uninstalling both the package that is depended upon
                        // and the parent package, so all dependencies are nulled
                        continue 2;
                    }
                }
                foreach (array('package', 'subpackage') as $packaged) {
                    $deps = $deppackage->dependencies['required']->$packaged;
                    if (isset($deps[$package->channel . '/' . $package->name])) {
                        $ret = $ret && $dep->validatePackageUninstall($deps[$package->channel . '/' . $package->name], $package);
                    }
                    $deps = $deppackage->dependencies['optional']->$packaged;
                    if (isset($deps[$package->channel . '/' . $package->name])) {
                        $ret = $ret && $dep->validatePackageUninstall($deps[$package->channel . '/' . $package->name], $package);
                    }
                }
            }
        }
        return $ret;
    }
}