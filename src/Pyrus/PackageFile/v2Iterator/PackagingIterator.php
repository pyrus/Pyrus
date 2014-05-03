<?php
/**
 * \Pyrus\PackageFile\v2Iterator\PackagingIterator
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

namespace Pyrus\PackageFile\v2Iterator;

/**
 * iterator for packaging
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class PackagingIterator extends \ArrayIterator
{
    static private $_parent;
    static function setParent(\Pyrus\PackageFileInterface $parent)
    {
        self::$_parent = $parent;
    }

    function key()
    {
        $curfile = $this->current();
        $role = \Pyrus\Installer\Role::factory(self::$_parent->getPackageType(), $curfile['attribs']['role']);
        // add the install-as attribute to retrieve packaging location
        return $role->getPackagingLocation(self::$_parent, $curfile['attribs']);
    }

    function current()
    {
        $curfile = parent::current();
        $curfile['attribs']['name'] = parent::key();
        if ($base = self::$_parent->getBaseInstallDir($curfile['attribs']['name'])) {
            $curfile['attribs']['baseinstalldir'] = $base;
        }

        if (isset($curfile['attribs']['md5sum'])) {
            unset($curfile['attribs']['md5sum']);
        }

        return $curfile;
    }
}