<?php
/**
 * \Pyrus\DER\OctetString
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

namespace Pyrus\DER;

/**
 * Represents a Distinguished Encoding Rule Octet String
 * 
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class OctetString extends \Pyrus\DER
{
    const TAG = 0x04;
    protected $value;

    function __construct($string = '')
    {
        $this->setValue($string);
    }

    function setValue($string)
    {
        $this->value = $string;
    }

    function serialize()
    {
        return $this->prependTLV($this->value, strlen($this->value));
    }

    function valueToString()
    {
        return bin2hex($this->value);
    }
}
