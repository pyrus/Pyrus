<?php
/**
 * \pear2\Pyrus\PackageFile\v2Iterator\FileAttribsFilter
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
 * Filter out the attributes meta-information when traversing the file list
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\v2Iterator;
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
