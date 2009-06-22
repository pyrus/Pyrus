<?php
/**
 * PEAR2_Pyrus_DER_IA5String
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
 * Represents a Distinguished Encoding Rule IA5String
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
abstract class PEAR2_Pyrus_DER_Constructed extends PEAR2_Pyrus_DER
{
    function parse($data, $location)
    {
        list($location, $length) = $this->decodeLength($data, $location);
        $data = substr($data, $location, $length);
        $location += $length;
        parent::parseFromString($data, $this);
        return $location;
    }

    function valueToString()
    {
        return '';
    }
}
