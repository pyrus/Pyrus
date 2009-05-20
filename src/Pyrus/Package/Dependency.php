<?php
/**
 * PEAR2_Pyrus_Package_Dependency
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
 * Class represents a package dependency.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Package_Dependency extends PEAR2_Pyrus_Package_Remote
{
    /**
     * A map of package name => packages that depend on this package =>
     * actual dependency
     */
    static protected $dependencyTree = array();
    
    /**
     * A map of package => packages it depends upon
     */
    static protected $packageDepTree = array();

    /**
     * Check to see if any packages in the list of packages to be installed
     * satisfy this dependency, and return one if found, otherwise
     * instantiate a new dependency package object
     * @return PEAR2_Pyrus_IPackage
     */
    static function retrieve(array $toBeInstalled, PEAR2_Pyrus_PackageFile_v2_Dependencies_Package $info,
                             PEAR2_Pyrus_Package $parentPackage)
    {
        static::processDependencies($info, $parentPackage);
        if (isset($toBeInstalled[$info->channel . '/' . $info->name])) {
            return $toBeInstalled[$info->channel . '/' . $info->name];
        }
        if (isset($info->uri)) {
            $ret = new PEAR2_Pyrus_Package_Remote($info->uri);
            // set up the basics
            $ret->name = $info->name;
            $ret->uri = $info->uri;
            return $ret;
        }
        return new PEAR2_Pyrus_Package_Remote($info->channel . '/' . $info->name);
    }

    /**
     * Create a tree mapping packages to those that depend on them
     *
     * This is used to determine which versions of a package satisfy
     * all package dependencies.  A composite dependency is calculated
     * from all of them, and this also allows removing a package from the
     * calculation
     */
    static protected function processDependencies($info, $parentPackage)
    {
        static::$dependencyTree[$info->channel . '/' . $info->name]
                               [$parentPackage->channel . '/' . $parentPackage->name] = $info;
        static::$packageDepTree[$parentPackage->channel . '/' . $parentPackage->name][]
            = $info->channel . '/' . $info->name;
    }

    /**
     * @return array A list of packages to be removed from the to-be-installed list
     */
    static function removePackage(PEAR2_Pyrus_Package $info)
    {
        var_dump('removing ' . $info->channel . '/' . $info->name);
        if (!isset(static::$packageDepTree[$info->channel . '/' . $info->name])) {
            return array();
        }
        $ret = array();
        foreach (static::$packageDepTree[$info->channel . '/' . $info->name] as $package) {
            unset(static::$dependencyTree[$package][$info->channel . '/' . $info->name]);
            if (!count(static::$dependencyTree[$package])) {
                $ret[] = $package;
                unset(static::$dependencyTree[$package]);
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
     */
    static function getCompositeDependency(PEAR2_Pyrus_Package $info)
    {
        if (!isset(static::$dependencyTree[$info->channel . '/' . $info->name])) {
            return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package(
                'required', 'package', null, array('name' => $info->name, 'channel' => $info->channel, 'uri' => null,
                                            'min' => null, 'max' => null,
                                            'recommended' => null, 'exclude' => null,
                                            'providesextension' => null, 'conflicts' => null), 0);
        }
        $compdep = array('name' => $info->name, 'channel' => $info->channel, 'uri' => null,
                                            'min' => null, 'max' => null,
                                            'recommended' => null, 'exclude' => null,
                                            'providesextension' => null, 'conflicts' => null);
        $initial = true;
        $recommended = null;
        $min = null;
        $max = null;
        foreach (static::$dependencyTree[$info->channel . '/' . $info->name] as $deppackage => $actualdep) {
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
                throw new PEAR2_Pyrus_Package_Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (different recommended values for ' .
                    $deppackage . ' and ' . $recommended . ')');
            }
            if ($compdep['max'] && $actualdep->min && version_compare($actualdep->min, $compdep['max'], '>')) {
                throw new PEAR2_Pyrus_Package_Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (' .
                    $deppackage . ' min is > ' . $max . ' max)');
            }
            if ($compdep['min'] && $actualdep->max && version_compare($actualdep->max, $compdep['min'], '<')) {
                throw new PEAR2_Pyrus_Package_Exception('Cannot install ' . $info->channel . '/' .
                    $info->name . ', two dependencies conflict (' .
                    $deppackage . ' max is > ' . $min . ' min)');
            }
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
            if ($actualdep->exclude) {
                if (!$compdep['exclude']) {
                    $compdep['exclude'] = array();
                    foreach ($actualdep->exclude as $exclude) {
                        $compdep['exclude'][] = $exclude;
                    }
                }
                foreach ($actualdep->exclude as $exclude) {
                    if (in_array($exclude, $compdep['exclude'])) {
                        continue;
                    }
                    $compdep['exclude'][] = $exclude;
                }
            }
        }
        return new PEAR2_Pyrus_PackageFile_v2_Dependencies_Package(
            'required', 'package', null, $compdep, 0);
    }
}