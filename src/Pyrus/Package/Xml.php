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
    function __construct($package, PEAR2_Pyrus_Package $parent)
    {
        $this->_file = $package;
        parent::__construct(new PEAR2_Pyrus_PackageFile($package), $parent);
    }

    function getLocation()
    {
        return dirname($this->packagefile->path);
    }

    function getFileContents($file, $asstream = false)
    {
        $file = dirname($this->_file) . DIRECTORY_SEPARATOR . $file;
        return ($asstream ? fopen($file, 'rb') : file_get_contents($file));
    }
}