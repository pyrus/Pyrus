<?php
/**
 * \Pyrus\Package\Remote
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Class representing a remote package
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Package;
use \Pyrus\Config as Config;
class Installed extends \Pyrus\Package\Base
{
    /**
     * The registry this installed package comes from
     * 
     * @var \Pyrus\Registry
     */
    protected $registry;
    
    function __construct(\Pyrus\PackageFile $packagefile, $parent = null, \Pyrus\Registry $registry)
    {
        $this->registry = $registry;
        parent::__construct($packagefile, $parent);

    }

    function getFilePath($file)
    {
        $role = \Pyrus\Installer\Role::factory($this->packagefile->getPackageType(), $this->packagefile->packagingcontents[$file]['attribs']['role']);
        list(, $path) = $role->getRelativeLocation($this->packagefile, new \Pyrus\PackageFile\v2Iterator\FileTag($this->packagefile->packagingcontents[$file], '', $this->packagefile), true);
        $dir = \Pyrus\Config::singleton($this->registry->getPath())->{$role->getLocationConfig()};
        return $dir . DIRECTORY_SEPARATOR . $path;
    }
    
    function copyTo($where)
    {
        throw new Exception('Not possible');
    }
}
