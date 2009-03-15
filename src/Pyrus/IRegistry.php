<?php
/**
 * PEAR2_Pyrus_IRegistry
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
 * Interface for a PEAR2 Pyrus managed installation registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
interface PEAR2_Pyrus_IRegistry
{
    public function install(PEAR2_Pyrus_IPackageFile $info, $replace = false);
    /**
     * Used by the registry package classes to update info in an installed package
     */
    public function replace(PEAR2_Pyrus_IPackageFile $info);
    public function uninstall($name, $channel);
    public function exists($package, $channel);
    public function info($package, $channel, $field);
    public function listPackages($channel);
    public function __get($var);
    /**
     * @return PEAR2_Pyrus_IPackageFile
     */
    public function toPackageFile($package, $channel);
    /**
     * Retrieve a list of package objects that depend on this package
     */
    public function getDependentPackages(PEAR2_Pyrus_Registry_Base $package);
}
