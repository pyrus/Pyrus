<?php
/**
 * \pear2\Pyrus\DER\BitString
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
 * Represents a Distinguished Encoding Rule Bit String
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus\DER;
class BitString extends \pear2\Pyrus\DER
{
    const TAG = 0x03;
    protected $value = false;
    protected $bitcount = 0;

    function __construct($string = '', $bits = 0)
    {
        $this->setValue($string, $bits);
    }

    function setValue($string, $bits = 0)
    {
        if (is_string($string)) {
            if (preg_match('/[01]+/', $string)) {
                $this->value = $string;
                $this->bitcount = strlen($string);
                return;
            }
        }
        $string = decbin(intval($string));
        if (!$bits) {
            $bits = strlen($string);
            $extra = 8 - $bits % 8;
            if ($extra === 8) {
                $extra = 0;
            }
            $bits += $extra;
            $string = str_repeat('0', $extra) . $string;
        }
        if (strlen($string) < $bits) {
            $string = str_repeat('0', $bits - strlen($string));
        } elseif (strlen($string) < $bits) {
            $string = substr($string, strlen($string) - $bits);
        }
        $this->bitcount = $bits;
        $this->value = $string;
    }

    function serialize()
    {
        // pad the string with zeros
        $extra = 8 - strlen($this->value) % 8;
        if ($extra === 8) {
            $extra = 0;
        }
        $string = $this->value . str_repeat('0', $extra);
        $string = base_convert($string, 2, 16);

        if (strlen($string) % 2) {
            $string = '0' . $string;
        }
        $hexlen = strlen($string) / 2;


        $value = '';
        for ($i = 0; $i < $hexlen; $i++) {
            $byte = hexdec(substr($string, $i * 2, 2));
            $value .= chr($byte);
        }
        // note the number of padding bits applied
        $value = chr($extra) . $value;
        return $this->prependTLV($value, strlen($value));
    }

    function parse($data, $location)
    {
        $ret = parent::parse($data, $location);
        $unusedbits = ord($this->value[0]);
        $value = substr($this->value, 1);
        $str = '';
        $strlen = strlen($value);
        for ($i = 0; $i < $strlen; $i++) {
            if ($i == $strlen - 1) {
                $binary = substr(decbin(ord($value[$i])), 0, 8 - $unusedbits);
            } else {
                $binary = decbin(ord($value[$i]));
            }
            $str .= $binary;
        }
        $this->value = $str;
        return $ret;
    }
}
