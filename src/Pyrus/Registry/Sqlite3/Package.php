<?php
/**
 * \pear2\Pyrus\Registry\Sqlite3\Package
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
 * Package within the sqlite3 registry
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\Registry\Sqlite3;
class Package extends \pear2\Pyrus\Registry\Package\Base
{
    public $dirty = false;

    function __set($var, $value)
    {
        if (!isset($this->packagename)) {
            throw new \pear2\Pyrus\Registry\Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        \pear2\Pyrus\PackageFile\v2::__set($var, $value);
        // occasionally, this next line will result in failure to install when incomplete data is there,
        // so we silently skip the save, and mark the packagefile as dirty
        try {
            $this->reg->replace($this);
            $this->dirty = false;
        } catch (\pear2\Pyrus\Registry\Exception $e) {
            $this->dirty = true;
        }
    }
}