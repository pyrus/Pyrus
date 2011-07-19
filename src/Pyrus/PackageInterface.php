<?php
/**
 * \Pyrus\PackageInterface
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

/**
 * Interface for packages
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
interface PackageInterface extends \ArrayAccess, \Pyrus\PackageFileInterface
{
    function getFileContents($file, $asstream = false);
    function getFilePath($file);
    function getFrom();
    function isStatic();
    function isUpgradeable();
    function __call($func, $args);
    /**
     * This allows a package to flexibly access its package.xml and return it
     * @return \Pyrus\PackageFileInterface
     */
    function getPackageFileObject();
    /**
     * Used by the download command to relocate a local tarball to the
     * download directory
     */
    function copyTo($where);
}
