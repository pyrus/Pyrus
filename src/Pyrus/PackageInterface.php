<?php
/**
 * \PEAR2\Pyrus\PackageInterface
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Interface for packages
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus;
interface PackageInterface extends \ArrayAccess, \PEAR2\Pyrus\PackageFileInterface
{
    function getFileContents($file, $asstream = false);
    function getFilePath($file);
    function getFrom();
    function isStatic();
    function isUpgradeable();
    function __call($func, $args);
    /**
     * This allows a package to flexibly access its package.xml and return it
     * @return \PEAR2\Pyrus\PackageFileInterface
     */
    function getPackageFileObject();
    /**
     * Used by the download command to relocate a local tarball to the
     * download directory
     */
    function copyTo($where);
}
