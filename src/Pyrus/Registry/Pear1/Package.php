<?php
/**
 * \Pyrus\Registry\Pear1\Package
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

namespace Pyrus\Registry\Pear1;

/**
 * Package within the PEAR 1.x registry
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class Package extends \Pyrus\Registry\Package\Base
{
    function fromPackageFile(\Pyrus\PackageFileInterface $package)
    {
        \Pyrus\PackageFile\v2::fromPackageFile($package);
        if (isset($this->packageInfo['contents']['dir']['attribs']['baseinstalldir'])) {
            $this->baseinstalldirs = array('/' => $this->packageInfo['contents']['dir']['attribs']['baseinstalldir']);
        }
    }
}