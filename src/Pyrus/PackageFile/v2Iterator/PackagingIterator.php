<?php
/**
 * \pear2\Pyrus\PackageFile\v2Iterator\PackagingIterator
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
 * iterator for packaging
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\v2Iterator;
class PackagingIterator extends \ArrayIterator
{
    static private $_parent;
    static function setParent(\pear2\Pyrus\PackageFileInterface $parent)
    {
        self::$_parent = $parent;
    }

    function key()
    {
        $curfile = $this->current();
        $role = \pear2\Pyrus\Installer\Role::factory(self::$_parent->getPackageType(), $curfile['attribs']['role']);
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