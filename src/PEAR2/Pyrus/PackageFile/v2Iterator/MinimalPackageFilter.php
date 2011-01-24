<?php
/**
 * \PEAR2\Pyrus\PackageFile\v2Iterator\MinimalPackageFilter
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
 * packaging filter for generating a package without any role=test or role=doc
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\PackageFile\v2Iterator;
class MinimalPackageFilter extends PackagingFilterBase
{
    function accept()
    {
        $it = $this->getInnerIterator();
        if (!$it->valid()) {
            return false;
        }

        $info = $it->current();
        if ($info['attribs']['role'] == 'doc' || $info['attribs']['role'] == 'test') {
            return false;
        }

        return true;
    }
}