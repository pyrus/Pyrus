<?php
/**
 * \Pyrus\DER\IA5String
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
 * Represents a Distinguished Encoding Rule IA5String
 * 
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\DER;
abstract class Constructed extends \Pyrus\DER
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
