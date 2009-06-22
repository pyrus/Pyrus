<?php
/**
 * PEAR2_Pyrus_DER_ObjectIdentifier
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
 * Represents a Distinguished Encoding Rule Object identifier
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_DER_ObjectIdentifier extends PEAR2_Pyrus_DER
{
    const TAG = 0x06;
    protected $value = '';
    protected $map = array(
        '1.3.14.3.2.26' => 'SHA-1 hash algorithm',
        '1.3.6.1.5.5.7.48.1.2' => 'OCSP nonce',
    );

    function __construct($value = '')
    {
        if ($value) {
            $this->setValue($value);
        }
    }

    function setValue($value)
    {
        if (!is_string($value)) {
            throw new PEAR2_Pyrus_DER_Exception('Object Identifier must be a string');
        }
        $value = explode('.', $value);
        foreach ($value as $val) {
            if (!preg_match('/[0-9]+/', $val)) {
                throw new PEAR2_Pyrus_DER_Exception('Object Identifier must be a period-delimited string of numbers');
            }
        }
        $this->value = $value;
    }

    function serialize()
    {
        $obj = $this->value;
        $value = chr($obj[0] * 40 + $obj[1]);
        $obj = array_slice($obj, 2);
        foreach ($obj as $node) {
            if ($node > 127) {
                // value is encoded in base 128, all significant bits set to 1 except
                // the last one, which is set to 0
                $node = intval($node);
                $components = array();
                while ($node) {
                    $components[] = $node % 128;
                    $node = floor($node / 128);
                }
                $components = array_reverse($components);
                $componentcount = count($components);
                for ($i = 0; $i < $componentcount; $i++) {
                    if ($i != $componentcount - 1) {
                        $components[$i] |= 0x80;
                    }
                    $value .= chr($components[$i]);
                }
            } else {
                $value .= chr((int) $node);
            }
        }
        return $this->prependTLV($value, strlen($value));
    }

    function parse($data, $location)
    {
        $ret = parent::parse($data, $location);
        $value = $this->value;
        $start = ord($value[0]);
        $first = floor($start / 40);
        $second = $start - $first * 40;
        $this->value = array($first, $second);
        $strlen = strlen($value);
        $long = false;
        $val = 0;
        for ($i = 1; $i < $strlen; $i++) {
            $current = ord($value[$i]);
            if (($current & 0x80) == 0x80) {
                if (!$long) {
                    $long = true;
                    $val = $current & 0x7F;
                    continue;
                }
            } elseif ($long) {
                $long = false;
                $val <<= 7;
                $val |= $current;
                $this->value[] = $val;
                $val = 0;
                continue;
            }
            if ($long) {
                $val <<= 7;
                $val |= $current & 0x7F;
            } else {
                $this->value[] = $current;
            }
        }
        return $ret;
    }

    function valueToString()
    {
        $ret = implode('.', $this->value);
        if (isset($this->map[$ret])) {
            $ret .= ' [' . $this->map[$ret] . ']';
        }
        return $ret;
    }
}