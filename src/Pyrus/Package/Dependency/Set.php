<?php
/**
 * \Pyrus\Dependency\Set
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Implements a set of dependency trees, and manipulates the trees to combine
 * them into a unique set of package releases to download
 *
 * This structure allows us to properly determine the best version, if any,
 * of a package that satisfies all dependencies of both packages to download and
 * installed packages.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\Package\Dependency;
class Set
{
    protected $packageTrees = array();
    protected $duplicates = array();
    protected $optionalDeps = array();

    function __construct(array $packages)
    {
        Set\PackageTree::setLocalPackages($packages);
        foreach ($packages as $package) {
            $tree = new Set\PackageTree($this, $package);
            $this->packageTrees[] = $tree;
            while (!$tree->populate()) {
                $this->refine();
            }
            //echo $tree; // uncomment to get a map of each separate dep tree
        }
    }

    function retrieveAllPackages()
    {
        $ret = array();
        $this->optionalDeps = Set\PackageTree::getUnusedOptionalDeps();
        foreach ($this->packageTrees as $tree) {
            foreach ($tree->getPackageSet() as $package) {
                $name = $package->name();
                $ret[$name] = $package->node;
                if (isset($this->optionalDeps[$name])) {
                    unset($this->optionalDeps[$name]);
                }
            }
        }

        return $ret;
    }

    function synchronizeDeps()
    {
        foreach ($this->packageTrees as $tree) {
            $tree->synchronize();
            //echo $tree; // uncomment to get a map of each separate dep tree
        }
    }

    function refine()
    {
        $dirty = Set\PackageTree::dirtyNodes();
        while (count($dirty)) {
            foreach ($dirty as $i => $node) {
                foreach ($this->packageTrees as $package) {
                    if (!$package->has($node)) {
                        continue;
                    }

                    $package->rebuildIfNecessary($node);
                }

                $dirty[$i] = null;
            }

            $dirty = array_filter($dirty);
        }
    }

    function getIgnoredOptionalDeps()
    {
        return $this->optionalDeps;
    }

    function getDependencies(\Pyrus\Package $info)
    {
        $deps = array();
        foreach ($this->packageTrees as $tree) {
            $deps = $tree->getDependencies($deps, $info->channel . '/' . $info->name);
        }

        return array_merge($deps, $this->getDependenciesOn($info));
    }

    function getDependenciesOn($info)
    {
        $name = $info->name;
        $channel = $info->channel;
        $packages = \Pyrus\Config::current()->registry
                            ->getDependentPackages($info->getPackageFileObject());

        $ret = array();
        foreach ($packages as $package) {
            $deps = $package->dependencies;
            foreach (array('package', 'subpackage') as $type) {
                foreach (array('required', 'optional') as $required) {
                    foreach ($deps[$required]->$type as $dep) {
                        if ($dep->channel != $channel || $dep->name != $name) {
                            continue;
                        }

                        $ret[] = $dep;
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Return a composite dependency on the package, as defined by combining
     * all dependencies on this package into one.
     *
     * As an example, for these dependencies:
     *
     * <pre>
     * P1 version >= 1.2.0
     * P1 version <= 3.0.0, != 2.3.2
     * P1 version >= 1.1.0, != 1.2.0
     * </pre>
     *
     * The composite dependency is
     *
     * <pre>
     * P1 version >= 1.2.0, <= 3.0.0, != 2.3.2, 1.2.0
     * </pre>
     * @param Pyrus\Package $info
     * @param bool $conflicting if true, return a composite <conflicts> dependency, if any
     */
    function getCompositeDependency(\Pyrus\Package $info, $conflicting = false)
    {
        $deps = $this->getDependencies($info);
        if (!count($deps)) {
            $dep = new \Pyrus\PackageFile\v2\Dependencies\Package(
                'required', 'package', null, array('name' => $info->name, 'channel' => $info->channel, 'uri' => null,
                                            'min' => null, 'max' => null,
                                            'recommended' => null, 'exclude' => null,
                                            'providesextension' => null, 'conflicts' => null), 0);
            $dep->setCompositeSources(array());
            return $dep;
        }

        $compdep = array('name' => $info->name, 'channel' => $info->channel, 'uri' => null,
                                            'min' => null, 'max' => null,
                                            'recommended' => null, 'exclude' => null,
                                            'providesextension' => null, 'conflicts' => null);

        $initial = true;
        $max = $min = $recommended = null;
        $useddeps = array();
        foreach ($deps as $actualdep) {
            if ($conflicting) {
                if (!$actualdep->conflicts) {
                    continue;
                }

                $compdep['conflicts'] = '';
            } elseif ($actualdep->conflicts) {
                continue;
            }

            $useddeps[] = $actualdep;
            $deppackage = $actualdep->getPackageFile()->channel . '/' .
                          $actualdep->getPackageFile()->name;
            if ($initial) {
                if ($actualdep->min) {
                    $compdep['min'] = $actualdep->min;
                    $min = $deppackage;
                }

                if ($actualdep->max) {
                    $compdep['max'] = $actualdep->max;
                    $max = $deppackage;
                }

                if ($actualdep->recommended) {
                    $compdep['recommended'] = $actualdep->recommended;
                    $recommended = $deppackage;
                }

                $compdep['exclude'] = $actualdep->exclude;
                $initial = false;
                continue;
            }

            if (isset($compdep['recommended']) && isset($actualdep->recommended)
                && $actualdep->recommended != $compdep['recommended']) {
                throw new \Pyrus\Package\Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (different recommended values for ' .
                    $deppackage . ' and ' . $recommended . ')');
            }

            if ($compdep['max'] && $actualdep->min && version_compare($actualdep->min, $compdep['max'], '>')) {
                throw new \Pyrus\Package\Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (' .
                    $deppackage . ' min is > ' . $max . ' max)');
            }

            if ($compdep['min'] && $actualdep->max && version_compare($actualdep->max, $compdep['min'], '<')) {
                throw new \Pyrus\Package\Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (' .
                    $deppackage . ' max is < ' . $min . ' min)');
            }

            if ($actualdep->min) {
                if ($compdep['min']) {
                    if (version_compare($actualdep->min, $compdep['min'], '>')) {
                        $compdep['min'] = $actualdep->min;
                        $min = $deppackage;
                    }
                } else {
                    $compdep['min'] = $actualdep->min;
                    $min = $deppackage;
                }
            }

            if ($actualdep->max) {
                if ($compdep['max']) {
                    if (version_compare($actualdep->max, $compdep['max'], '<')) {
                        $compdep['max'] = $actualdep->max;
                        $max = $deppackage;
                    }
                } else {
                    $compdep['max'] = $actualdep->max;
                    $max = $deppackage;
                }
            }

            if ($actualdep->recommended) {
                $compdep['recommended'] = $actualdep->recommended;
                $recommended = $deppackage;
            }

            if ($actualdep->exclude) {
                if (!$compdep['exclude']) {
                    $compdep['exclude'] = array();
                    foreach ($actualdep->exclude as $exclude) {
                        $compdep['exclude'][] = $exclude;
                    }

                    continue;
                }

                foreach ($actualdep->exclude as $exclude) {
                    if (in_array($exclude, $compdep['exclude'])) {
                        continue;
                    }
                    $compdep['exclude'][] = $exclude;
                }
            }
        }

        $dep = new \Pyrus\PackageFile\v2\Dependencies\Package(
            'required', 'package', null, $compdep, 0);
        $dep->setCompositeSources($useddeps);
        return $dep;
    }
}