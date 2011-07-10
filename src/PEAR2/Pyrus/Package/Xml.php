<?php
/**
 * \Pyrus\Package\Xml
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
 * Package represented just by the package.xml file
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\Package;
class Xml extends \Pyrus\Package\Base
{
    function __construct($package, \Pyrus\Package $parent, \Pyrus\PackageFile $info = null)
    {
        $this->archive = $package;
        if ($info === null) {
            $info = new \Pyrus\PackageFile($package);
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
        throw new Exception('download/copy not supported for extracted packages');
    }

    function getFilePath($file)
    {
        return dirname($this->archive) . DIRECTORY_SEPARATOR . str_replace(array('\\','/'), DIRECTORY_SEPARATOR, $file);
    }
}
