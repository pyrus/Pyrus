<?php
/**
 * PEAR2_Pyrus_Registry_Pear1_Package
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
 * Package within the PEAR 1.x registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Registry_Pear1_Package extends PEAR2_Pyrus_Registry_Package_Base
{
    function fromPackageFile(PEAR2_Pyrus_IPackageFile $package)
    {
        PEAR2_Pyrus_PackageFile_v2::fromPackageFile($package);
        if (isset($this->packageInfo['contents']['dir']['attribs']['baseinstalldir'])) {
            $this->baseinstalldirs = array('/' => $this->packageInfo['contents']['dir']['attribs']['baseinstalldir']);
        }
    }
}