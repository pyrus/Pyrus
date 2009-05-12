<?php
/**
 * PEAR2_Pyrus_IPackage
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
interface PEAR2_Pyrus_IPackage extends ArrayAccess, PEAR2_Pyrus_IPackageFile
{
    function getFileContents($file, $asstream = false);
    function getFilePath($file);
    function getLocation();
    function getFrom();
    function __call($func, $args);
    /**
     * This allows a package to flexibly access its package.xml and return it
     * @return PEAR2_Pyrus_IPackageFile
     */
    function getPackageFileObject();
}
