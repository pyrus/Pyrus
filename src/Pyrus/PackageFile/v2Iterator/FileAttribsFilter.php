<?php
/**
 * \Pyrus\PackageFile\v2Iterator\FileAttribsFilter
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

/**
 * Filter out the attributes meta-information when traversing the file list
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\PackageFile\v2Iterator;
class FileAttribsFilter extends \RecursiveFilterIterator
{
    function accept()
    {
        $it = $this->getInnerIterator();
        if (!$it->valid()) {
            return false;
        }

        $key = $it->key();
        if ($key === 'attribs') {
            return false;
        }

        return true;
    }
}
