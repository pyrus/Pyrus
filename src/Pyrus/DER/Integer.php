<?php
/**
 * \pear2\Pyrus\DER\Integer
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
 * Represents a Distinguished Encoding Rule Integer
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\DER;
class Integer extends \pear2\Pyrus\DER
{
    const TAG = 0x02;
    protected $value;

    function __construct($int = 0)
    {
        $this->setValue($int);
    }

    function setValue($int)
    {
        if (!is_string($int)) {
            $this->value = intval($int);
        } else {
            $this->value = $int;
        }
    }

    function serialize()
    {
        if (is_string($this->value)) {
            return $this->prependTLV($value, strlen($value));
        }

        if ($this->value < 0) {
            $hexvalue = dechex(-$this->value);
        } else {
            $hexvalue = dechex($this->value);
        }

        if (strlen($hexvalue) % 2) {
            $hexvalue = '0' . $hexvalue;
        }
        $hexlen = strlen($hexvalue) / 2;


        $value = '';
        for ($i = 0; $i < $hexlen; $i++) {
            $byte = hexdec(substr($hexvalue, $i * 2, 2));
            if ($this->value < 0) {
                // ones complement
                $byte ^=0xFF;
                if ($i == $hexlen - 1) {
                    // add 1 to LSB for twos complement
                    $byte += 1;
                }
                if ($i == 0) {
                    if (($byte & 0x80) !== 0x80) {
                        // we must have a leading 0xFF if the number doesn't
                        // start with a leading bit, otherwise we become non-negative
                        $value .= "\777";
                    }
                }
            }
            if ($this->value > 0 && !$i) {
                if (($byte & 0x80) === 0x80) {
                    // leading 0 so that this is not interpreted as a negative number
                    $value .= "\0";
                }
            }
            $value .= chr($byte);
        }

        return $this->prependTLV($value, strlen($value));
    }

    function parse($data, $location) {
        $ret = parent::parse($data, $location);
        $value = $this->value;
        $int = 0;
        $negative = ($value[0] & 0x80) === 0x80;
        if ($negative) {
            $value[strlen($value) - 1] = ord($value[strlen($value) - 1]) - 1;
            for ($i = 0; $i < strlen($value); $i++) {
                $value[$i] = ord($value[$i]) ^ 0xFF;
            }
        }
        for ($i = 0; $i < strlen($value); $i++) {
            $int <<= 8;
            $int += ord($value[$i]);
        }
        $this->value = $negative ? -$int : $int;
        return $ret;
    }
}
