<?php
/**
 * PEAR2_Pyrus_Registry_Sqlite3_Package
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
class PEAR2_Pyrus_Registry_Sqlite3_Package extends PEAR2_Pyrus_Registry_Package_Base
{
    public $dirty = false;
    function __construct(PEAR2_Pyrus_Registry_Sqlite3 $cloner)
    {
        $this->reg = $cloner;
    }

    function __set($var, $value)
    {
        if (!isset($this->packagename)) {
            throw new PEAR2_Pyrus_Registry_Exception('Attempt to retrieve ' . $var .
                ' from unknown package');
        }
        PEAR2_Pyrus_PackageFile_v2::__set($var, $value);
        // occasionally, this next line will result in failure to install when incomplete data is there,
        // so we silently skip the save, and mark the packagefile as dirty
        try {
            $this->reg->replace($this);
            $this->dirty = false;
        } catch (PEAR2_Pyrus_Registry_Exception $e) {
            $this->dirty = true;
        }
    }
}