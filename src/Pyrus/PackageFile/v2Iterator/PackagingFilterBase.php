<?php
/**
 * \PEAR2\Pyrus\PackageFile\v2Iterator\PackagingFilterBase
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
 * base class for filtering packaging contents to modify the files used from
 * a package.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\PackageFile\v2Iterator;
abstract class PackagingFilterBase extends \FilterIterator
{
    function __construct(PackagingIterator $iterator)
    {
        parent::__construct($iterator);
    }

    final function getIterator(PackagingIterator $iterator, $extra1 = null, $extra2 = null, $extra3 = null)
    {
        return new static($iterator, $extra1, $extra2, $extra3);
    }
}