<?php
/**
 * \pear2\Pyrus\DER\OctetString
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
 * Represents a Distinguished Encoding Rule Octet String
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\DER;
class OctetString extends \pear2\Pyrus\DER
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
