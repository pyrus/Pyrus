<?php
/**
 * \Pyrus\Registry\Sqlite3\Package
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
 * Package within the sqlite3 registry
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Registry\Sqlite3;
class Package extends \Pyrus\Registry\Package\Base
{
    public $dirty = false;

    function __set($var, $value)
    {
        if (!isset($this->packagename)) {
            throw new \Pyrus\Registry\Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        \Pyrus\PackageFile\v2::__set($var, $value);
        // occasionally, this next line will result in failure to install when incomplete data is there,
        // so we silently skip the save, and mark the packagefile as dirty
        try {
            $this->reg->replace($this);
            $this->dirty = false;
        } catch (\Pyrus\Registry\Exception $e) {
            $this->dirty = true;
        }
    }
}