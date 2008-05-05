<?php
/**
 * PEAR2_Pyrus_PackageFile
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
 * Base class for a PEAR2 package file
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PackageFile
{
    public $info;
    public $path;
    function __construct($package, $class = 'PEAR2_Pyrus_PackageFile_v2')
    {
        $this->path = $package;
        $parser = new PEAR2_Pyrus_PackageFile_Parser_v2;
        $data = file_get_contents($package);
        $this->info = $parser->parse($data, $package, $class);
    }

    function __toString()
    {
        return $this->info->__toString();
    }
}
