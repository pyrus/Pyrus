<?php
/**
 * \pear2\Pyrus\IPackage
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
 * Interface for packages
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus;
interface IPackage extends \ArrayAccess, \pear2\Pyrus\IPackageFile
{
    function getFileContents($file, $asstream = false);
    function getFilePath($file);
    function getFrom();
    function isStatic();
    function isUpgradeable();
    function __call($func, $args);
    /**
     * This allows a package to flexibly access its package.xml and return it
     * @return \pear2\Pyrus\IPackageFile
     */
    function getPackageFileObject();
    /**
     * Used by the download command to relocate a local tarball to the
     * download directory
     */
    function copyTo($where);
}
