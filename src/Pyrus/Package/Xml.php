<?php
/**
 * PEAR2_Pyrus_Package_Xml
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
 * Package represented just by the package.xml file
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Package_Xml extends PEAR2_Pyrus_Package_Base
{
    private $_file;
    function __construct($package, PEAR2_Pyrus_Package $parent, PEAR2_Pyrus_PackageFile $info = null)
    {
        $this->_file = $package;
        if ($info === null) {
            $info = new PEAR2_Pyrus_PackageFile($package);
        }
        parent::__construct($info, $parent);
    }

    /**
     * This test tells the installer whether to run any package-info
     * replacement tasks.
     *
     * The XML package has not had any package-info transformations.  Packages
     * in tar/zip/phar format have had package-info replacements.
     * @return bool if false, the installer will run all packag-einfo replacements
     */
    function isPreProcessed()
    {
        return false;
    }

    function copyTo($where)
    {
        throw new PEAR2_Pyrus_Package_Exception('download/copy not supported for extracted packages');
    }

    function getFilePath($file)
    {
        return dirname($this->_file) . DIRECTORY_SEPARATOR . $file;
    }
}
