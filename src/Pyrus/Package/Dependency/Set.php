<?php
/**
 * \pear2\Pyrus\Dependency\Set
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
 * Implements a set of dependency trees, and manipulates the trees to combine
 * them into a unique set of package releases to download
 *
 * This structure allows us to properly determine the best version, if any,
 * of a package that satisfies all dependencies of both packages to download and
 * installed packages.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\Package\Dependency;
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
                if (isset($this->duplicates[$package->name()])) {
                    $ret[$package->name()] = $this->duplicates[$package->name()]->primaryNode()->node;
                } else {
                    $ret[$package->name()] = $package->node;
                }
                if (isset($this->optionalDeps[$package->name()])) {
                    unset($this->optionalDeps[$package->name()]);
                }
            }
        }
        return $ret;
    }

    function synchronizeDeps()
    {
        foreach ($this->packageTrees as $tree) {
            $tree->synchronize();
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

    /**
     * Merge all duplicate packages in each tree into 1 release version, if possible.
     *
     * This is a 4-step process
     *
     *  1. find duplicates
     *  2. if explicitly requested version, resolve in favor of an
     *     explicitly requested version and jump to step 4
     *  3. if no explicit version, resolve in favor of the newest version
     *  4. re-process composite dependencies.  On fail, either go to
     *     step 3 if we came from step 3, or fail
     */
    function resolveDuplicates()
    {
        $duplicates = $this->findDuplicates();
        $this->duplicates = $duplicates;
        foreach ($duplicates as $name => $dupes) {
            if ($dupes->allSameVersion()) {
                // all deps resolved to the same version, we're good to go
                continue;
            }
            // $dupes is a pear2\Pyrus\Package\DuplicateDependency
            if ($dupes->isExplicitVersion()) {
                foreach ($this->packageTrees as $package) {
                    if (!$package->has($dupes->primaryNode())) {
                        continue;
                    }
                    $package->rebuildIfNecessary($dupes->primaryNode());
                    continue 2;
                }
            } else {
                do {
                    try {
                        foreach ($this->packageTrees as $package) {
                            if (!$package->has($dupes->primaryNode())) {
                                continue;
                            }
                            $package->rebuildIfNecessary($dupes->primaryNode());
                            continue 3;
                        }
                    } catch (Set\Exception $e) {
                        $dupes->failCurrent();
                    }
                } while ($dupes->possible());
                // XX TODO: make this error message smarter
                throw new Set\Exception('Impossible dependency conflict');
            }
        }
    }

    function getIgnoredOptionalDeps()
    {
        return $this->optionalDeps;
    }

    function findDuplicates()
    {
        $sets = array();
        foreach ($this->packageTrees as $tree) {
            $sets[] = $tree->getPackageSet();
        }
        if (!count($sets)) {
            return $sets;
        }
        $keys = array_map('array_keys', $sets);
        $merged = call_user_func_array('array_merge', $keys);
        $count = array_count_values($merged);
        $dupenames = array_keys(array_filter($count,
                              function ($a) {return $a > 1;}));
        $dupes = array();
        foreach ($sets as $tree) {
            foreach ($tree as $name => $package) {
                if (!in_array($name, $dupenames)) {
                    continue;
                }
                if (!isset($dupes[$name])) {
                    $dupes[$name] = array();
                }
                $dupes[$name][] = $package;
            }
        }
        $ret = array();
        foreach ($dupes as $name => $packages) {
            $ret[$name] = new \pear2\Pyrus\Package\DuplicateDependency($packages);
        }
        return $ret;
    }

    function getDependencies(\pear2\Pyrus\Package $info)
    {
        $deps = array();
        foreach ($this->packageTrees as $tree) {
            $deps = $tree->getDependencies($deps, $info->channel . '/' . $info->name);
        }
        return array_merge($deps,
                           $this->getDependenciesOn($info));
    }

    function getDependenciesOn($info)
    {
        $name = $info->name;
        $channel = $info->channel;
        $packages = \pear2\Pyrus\Config::current()->registry
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
     * @param pear2\Pyrus\Package $info
     * @param bool $conflicting if true, return a composite <conflicts> dependency, if any
     */
    function getCompositeDependency(\pear2\Pyrus\Package $info, $conflicting = false)
    {
        $deps = $this->getDependencies($info);
        if (!count($deps)) {
            $dep = new \pear2\Pyrus\PackageFile\v2\Dependencies\Package(
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
        $recommended = null;
        $min = null;
        $max = null;
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
                throw new \pear2\Pyrus\Package\Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (different recommended values for ' .
                    $deppackage . ' and ' . $recommended . ')');
            }
            if ($compdep['max'] && $actualdep->min && version_compare($actualdep->min, $compdep['max'], '>')) {
                throw new \pear2\Pyrus\Package\Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (' .
                    $deppackage . ' min is > ' . $max . ' max)');
            }
            if ($compdep['min'] && $actualdep->max && version_compare($actualdep->max, $compdep['min'], '<')) {
                throw new \pear2\Pyrus\Package\Exception('Cannot install ' . $info->channel . '/' .
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
        $dep = new \pear2\Pyrus\PackageFile\v2\Dependencies\Package(
            'required', 'package', null, $compdep, 0);
        $dep->setCompositeSources($useddeps);
        return $dep;
    }
}