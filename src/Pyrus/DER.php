<?php
/**
 * PEAR2_Pyrus_DER
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
 * Implements Distinguished Encoding Rules serialization and unserialization.
 *
 * This is used by OpenSSL's OCSP protocol to verify a certificate, and is
 * used by Pyrus to validate a package's OpenSSL public key to implement
 * native package signing.
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 * @link      http://www.oss.com/asn1/dubuisson.html
 */
class PEAR2_Pyrus_DER implements ArrayAccess
{
    const SEQUENCE = 0x30;
    const SET = 0x31;

    protected $depth = 0;
    protected $schema;
    protected $value;
    protected $objs = array();
    protected $tagMap = array(
        PEAR2_Pyrus_DER_BitString::TAG => 'PEAR2_Pyrus_DER_BitString',
        PEAR2_Pyrus_DER_BMPString::TAG => 'PEAR2_Pyrus_DER_BMPString',
        PEAR2_Pyrus_DER_Boolean::TAG => 'PEAR2_Pyrus_DER_Boolean',
        PEAR2_Pyrus_DER_Enumerated::TAG => 'PEAR2_Pyrus_DER_Enumerated',
        PEAR2_Pyrus_DER_GeneralizedTime::TAG => 'PEAR2_Pyrus_DER_GeneralizedTime',
        PEAR2_Pyrus_DER_IA5String::TAG => 'PEAR2_Pyrus_DER_IA5String',
        PEAR2_Pyrus_DER_Integer::TAG => 'PEAR2_Pyrus_DER_Integer',
        PEAR2_Pyrus_DER_Null::TAG => 'PEAR2_Pyrus_DER_Null',
        PEAR2_Pyrus_DER_NumericString::TAG => 'PEAR2_Pyrus_DER_NumericString',
        PEAR2_Pyrus_DER_ObjectIdentifier::TAG => 'PEAR2_Pyrus_DER_ObjectIdentifier',
        PEAR2_Pyrus_DER_OctetString::TAG => 'PEAR2_Pyrus_DER_OctetString',
        PEAR2_Pyrus_DER_PrintableString::TAG => 'PEAR2_Pyrus_DER_PrintableString',
        PEAR2_Pyrus_DER_Sequence::TAG => 'PEAR2_Pyrus_DER_Sequence',
        PEAR2_Pyrus_DER_Set::TAG => 'PEAR2_Pyrus_DER_Set',
        PEAR2_Pyrus_DER_UniversalString::TAG => 'PEAR2_Pyrus_DER_UniversalString',
        PEAR2_Pyrus_DER_UTCTime::TAG => 'PEAR2_Pyrus_DER_UTCTime',
        PEAR2_Pyrus_DER_UTF8String::TAG => 'PEAR2_Pyrus_DER_UTF8String',
        PEAR2_Pyrus_DER_VisibleString::TAG => 'PEAR2_Pyrus_DER_VisibleString',
    );

    static function factory()
    {
        return new static;
    }

    function setDepth($d)
    {
        $this->depth = $d;
    }

    function setSchema(PEAR2_Pyrus_DER_Schema $schema)
    {
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    function serialize()
    {
        $val = '';
        foreach ($this->objs as $i => $obj) {
            $val .= chr($obj->tag());
            $val .= $obj->serialize();
        }
        return $val;
    }

    protected function tag()
    {
        if (isset($this->schema) && $tag = $this->schema->tag) {
            return $tag;
        }
        return $this::TAG;
    }

    function __get($name)
    {
        if (!isset($this->schema)) {
            throw new PEAR2_Pyrus_DER_Exception('To access objects, use ArrayAccess when schema is not set');
        }
        if (!isset($this->schema[$name])) {
            throw new PEAR2_Pyrus_DER_Exception('schema has no element matching ' . $name .
                                                ' at ' . $this->schema->path());
        }
        $info = $this->schema[$name];
        $class = $info->type;
        $index = $info->name;
        if (!isset($this[$index])) {
            $this[$index] = new $class;
            $this[$index]->setSchema($info);
        }
        return $this[$index];
    }

    function __set($name, $value)
    {
        if (!isset($this->schema)) {
            throw new PEAR2_Pyrus_DER_Exception('To access objects, use ArrayAccess when schema is not set');
        }
        if (!isset($this->schema[$name])) {
            throw new PEAR2_Pyrus_DER_Exception('schema has no element matching ' . $name .
                                                ' at ' . $this->schema->path());
        }
        $info = $this->schema[$name];
        $class = $info->type;
        $index = $info->name;
        if (!isset($this[$index])) {
            $this[$index] = new $class;
            $this[$index]->setSchema($info);
        }
        $this[$index]->setValue($value);
    }

    function prependTLV($value, $length)
    {
        $ret = '';
        if ($length <= 127) {
            $ret .= chr($length);
        } else {
            $newlen = dechex($length);
            if (strlen($newlen) % 2) {
                $newlen = "0$newlen";
            }
            $lengthlen = strlen($newlen) / 2;
            $ret .= chr(0x80 | $lengthlen);
            $new = '';
            for ($i = 0; $i < $lengthlen; $i++) {
                $ret .= chr(hexdec(substr($newlen, $i * 2, 2)));
            }
            $ret .= $new;
        }
        $ret .= $value;
        return $ret;
    }

    function _bitString($string = '', $bits = 0)
    {
        $obj = new PEAR2_Pyrus_DER_BitString($string, $bits);
        $this->objs[] = $obj;
        return $this;
    }

    function _null()
    {
        $obj = new PEAR2_Pyrus_DER_Null;
        $this->objs[] = $obj;
        return $this;
    }

    function __call($func, $args)
    {
        if (strtolower($func) == 'bitstring') {
            return call_user_func_array(array($this, '_bitString'), $args);
        }
        if (strtolower($func) == 'null') {
            return $this->_null();
        }
        $class = 'PEAR2_Pyrus_DER_' . ucfirst($func);
        if (!class_exists($class, 1)) {
            throw new PEAR2_Pyrus_DER_Exception('Unknown type ' . $func);
        }
        if (isset($args[0])) {
            $obj = new $class($args[0]);
        } else {
            $obj = new $class;
        }
        $this->objs[] = $obj;
        return $this;
    }

    function constructed(PEAR2_Pyrus_DER $der)
    {
        $this->objs[] = $der;
        return $this;
    }

    function addMultiple($index, $obj)
    {
        if (!isset($this->objs[$index])) {
            // essentially use this as an array of values
            $this->objs[$index] = $obj;
        } elseif (get_class($this->objs[$index]) != 'PEAR2_Pyrus_DER') {
            $current = $this->objs[$index];
            $this->objs[$index] = new PEAR2_Pyrus_DER;
            $this->objs[$index]->setDepth($this->depth);
            $this->objs[$index][] = $current;
            $this->objs[$index][] = $obj;
        } else {
            $this->objs[$index][] = $obj;
        }
    }

    function offsetGet($var)
    {
        return $this->objs[$var];
    }

    function offsetSet($var, $value)
    {
        if ($var === null) {
            $var = count($this->objs);
        }
        $this->objs[$var] = $value;
    }

    function offsetUnset($var)
    {
        throw new PEAR2_Pyrus_DER_Exception('offsetUnset not possible');
    }

    function offsetExists($var)
    {
        return isset($this->objs[$var]);
    }

    function type()
    {
        return substr(get_class($this), strlen('PEAR2_Pyrus_DER_'));
    }

    function isType($tag)
    {
        return $this::tag === $tag;
    }

    function decodeLength($data, $location)
    {
        if ((0x80 & ord($data[$location])) == 0x80) {
            $lengthbytes = 0x7F & ord($data[$location]);
            $hex = substr($data, $location + 1, $lengthbytes);
            $multiplier = 1;
            $length = 0;
            for ($i = $lengthbytes - 1; $i >= 0; $i--) {
                $digit = ord($hex[$i]);
                $length += $digit * $multiplier;
                $multiplier *= 0x100;
            }
            $location += $lengthbytes + 1;
        } else {
            $length = ord($data[$location++]);
        }
        return array($location, $length);
    }

    function parse($data, $location)
    {
        list($location, $length) = $this->decodeLength($data, $location);
        $this->value = substr($data, $location, $length);
        $location += $length;
        return $location;
    }

    function parseFromString($data, PEAR2_Pyrus_DER $parent = null)
    {
        if (null === $parent) {
            $parent = $this;
        }
        $location = 0;
        $strlen = strlen($data);
        $index = 0;
        do {
            $tag = ord($data[$location]);
            if (isset($this->schema)) {
                if (!($info = $this->schema->find($tag))) {
                    throw new PEAR2_Pyrus_DER_Exception('schema has no ' .
                                                        'tag matching ' . dechex($tag) .
                                                        ' at ' . $this->schema->path());
                }
                $class = $info->type;
                $index = $info->name;
                if ($info->multiple()) {
                    $parent->addMultiple($index, $obj = new $class);
                } else {
                    $parent[$index] = $obj = new $class;
                }
                $obj->setSchema($info);
            } else {
                // no schema
                if (($tag & 0x80) === 0x80) {
                    // context-sensitive tag, do best guess
                    if (($tag & 0x20) == 0x20) {
                        $tag = PEAR2_Pyrus_DER_Sequence::TAG;
                    } else {
                        $tag = PEAR2_Pyrus_DER_OctetString::TAG;
                    }
                }
                if (!isset($this->tagMap[$tag])) {
                    throw new PEAR2_Pyrus_DER_Exception('Unknown tag: ' . dechex($tag));
                }
                $type = $this->tagMap[$tag];
                $parent[$index] = $obj = new $type;
            }

            $obj->setDepth($this->depth + 1);
            $location = $obj->parse($data, $location + 1);
            if (!isset($this->schema)) {
                $index++;
            }
        } while ($location < $strlen);
        if (isset($this->schema)) {
            $this->schema->resetLastFind();
        }
    }

    function __toString()
    {
        if (count($this->objs)) {
            if (isset($this->schema)) {
                if ($this->schema->name) {
                    $ret = str_repeat(' ', $this->depth) . $this->schema->name . " [";
                    $ret .=
                        lcfirst(str_replace('PEAR2_Pyrus_DER_', '', get_class($this))) .
                        "]: ";
                } else {
                    $ret = '';
                }
            } elseif (get_class($this) != 'PEAR2_Pyrus_DER') {
                $ret = str_repeat(' ', $this->depth);
                $ret .=
                    lcfirst(str_replace('PEAR2_Pyrus_DER_', '', get_class($this))) .
                    ": ";
            } else {
                $ret = str_repeat(' ', $this->depth) . '(multiple): ';
            }

            foreach ($this->objs as $obj) {
                $ret .= "\n" . $obj;
            }
            if (isset($obj) && !($obj instanceof PEAR2_Pyrus_DER_Constructed)) {
                $ret .= "\n";
            }
            if (isset($this->schema)) {
                $ret .= str_repeat(' ', $this->depth) .
                    "end " . $this->schema->name . "\n";
            } elseif (get_class($this) === 'PEAR2_Pyrus_DER') {
                $ret .= str_repeat(' ', $this->depth) . "end (multiple)\n";
            } else {
                $ret .= str_repeat(' ', $this->depth) .
                    "end " . lcfirst(str_replace('PEAR2_Pyrus_DER_', '', get_class($this))) . "\n";
            }
            return $ret;
        }
        if (isset($this->schema)) {
            return str_repeat(' ', $this->depth) . $this->schema->name . ' [' .
                lcfirst(str_replace('PEAR2_Pyrus_DER_', '', get_class($this))) . '] ' .
                '(' . $this->valueToString() . ')';
        }
        if (get_class($this) === 'PEAR2_Pyrus_DER') {
            return str_repeat(' ', $this->depth) . '[]';
        }
        return str_repeat(' ', $this->depth) .
            lcfirst(str_replace('PEAR2_Pyrus_DER_', '', get_class($this))) .
            '(' . $this->valueToString() . ')';
    }

    function valueToString()
    {
        return $this->value;
    }

    function getValue()
    {
        return $this->value;
    }
}