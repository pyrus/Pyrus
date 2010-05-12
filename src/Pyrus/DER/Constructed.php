<?php
/**
 * \PEAR2\Pyrus\DER\IA5String
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
 * Represents a Distinguished Encoding Rule IA5String
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\DER;
abstract class Constructed extends \PEAR2\Pyrus\DER
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
