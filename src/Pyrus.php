<?php
/**
 * PEAR2_Pyrus
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
 * Base class for Pyrus.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus
{

    /**
     * Installer options.  Valid indices are:
     *
     * - upgrade (upgrade or install packages)
     * - optionaldeps (also automatically download/install optional deps)
     * - force
     * - packagingroot
     * - install-plugins
     * - nodeps
     * - downloadonly
     * @var array
     */
    public static $options = array();

    static function getDataPath()
    {
        static $val = false;
        if ($val) return $val;
        $val = dirname(dirname(__DIR__)) . '/data/pear2.php.net/PEAR2_Pyrus';
        return $val;
    }

    static function prepend($prepend, $path)
    {
        $path = $prepend . DIRECTORY_SEPARATOR . $path;
        $path = preg_replace('@/+|\\\\+@', DIRECTORY_SEPARATOR, $path);
        return $path;
    }
}
?>
