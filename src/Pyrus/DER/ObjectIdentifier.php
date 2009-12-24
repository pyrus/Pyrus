<?php
/**
 * \pear2\Pyrus\DER\ObjectIdentifier
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
namespace pear2\Pyrus\DER;
class ObjectIdentifier extends \pear2\Pyrus\DER
{
    const TAG = 0x06;
    protected $value = '';
    protected $map = array(
        '1.2.840.113549.1.1.1' => 'RSA encryption',
        '1.2.840.113549.1.1.5' => 'SHA-1 checksum with RSA encryption',
        '1.2.840.113549.1.9.1' => 'Email (for use in signatures)',
        '1.3.6.1.5.5.7.48.1' => 'OCSP',
        '1.3.6.1.5.5.7.48.1.1' => 'OCSP basic response',
        '1.3.6.1.5.5.7.48.1.2' => 'OCSP nonce',
        '1.3.14.3.2.26' => 'SHA-1 hash algorithm',
        '2.5.4.3' => 'Common Name',
        '2.5.4.6' => 'Country Name',
        '2.5.4.7' => 'Locality (City) Name',
        '2.5.4.8' => 'State/Province Name',
        '2.5.4.10' => 'Organization Name',
        '2.5.4.11' => 'Organization Web Site',
        '2.5.29.37' => 'Extended Key Usage',
        '2.5.29.17' => 'Subject Alternative Name',
        '2.5.29.19' => 'Basic Constraints - can it be a CA?'
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
            throw new Exception('Object Identifier must be a string');
        }
        $value = explode('.', $value);
        foreach ($value as $val) {
            if (!preg_match('/[0-9]+/', $val)) {
                throw new Exception('Object Identifier must be a period-delimited string of numbers');
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
