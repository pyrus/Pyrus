<?php
/**
 * \Pyrus\PackageFile\v2Iterator\MinimalPackageFilter
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
 * packaging filter for generating a package without any role=test or role=doc
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
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