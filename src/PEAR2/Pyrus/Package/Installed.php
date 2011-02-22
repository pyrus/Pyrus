<?php
/**
 * \PEAR2\Pyrus\Package\Remote
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
 * Class representing a remote package
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\Package;
use \PEAR2\Pyrus\Config as Config;
class Installed extends \PEAR2\Pyrus\Package\Base
{
    /**
     * The registry this installed package comes from
     * 
     * @var \PEAR2\Pyrus\Registry
     */
    protected $registry;
    
    function __construct(\PEAR2\Pyrus\PackageFile $packagefile, $parent = null, \PEAR2\Pyrus\Registry $registry)
    {
        $this->registry = $registry;
        parent::__construct($packagefile, $parent);

    }

    function getFilePath($file)
    {
        $role = \PEAR2\Pyrus\Installer\Role::factory($this->packagefile->getPackageType(), $this->packagefile->packagingcontents[$file]['attribs']['role']);
        list(, $path) = $role->getRelativeLocation($this->packagefile, new \PEAR2\Pyrus\PackageFile\v2Iterator\FileTag($this->packagefile->packagingcontents[$file], '', $this->packagefile), true);
        $dir = \PEAR2\Pyrus\Config::singleton($this->registry->getPath())->{$role->getLocationConfig()};
        return $dir . DIRECTORY_SEPARATOR . $path;
    }
    
    function copyTo($where)
    {
        throw new Exception('Not possible');
    }
}
