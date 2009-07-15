<?php
/**
 * \pear2\Pyrus\DER
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
namespace pear2\Pyrus;
class DER implements \ArrayAccess
{
    const SEQUENCE = 0x30;
    const SET = 0x31;

    protected $depth = 0;
    /**
     * If value was parsed, this contains the absolute offset within the parsed
     * data.
     *
     * This is very useful for calculating signature of an OCSP response, as
     * we simply find the offset of the signature, and check everything before it
     * @var int
     */
    protected $offset;
    protected $schema;
    protected $value;
    protected $objs = array();
    protected $tagMap = array(
        \pear2\Pyrus\DER\BitString::TAG => 'pear2\Pyrus\DER\BitString',
        \pear2\Pyrus\DER\BMPString::TAG => 'pear2\Pyrus\DER\BMPString',
        \pear2\Pyrus\DER\Boolean::TAG => 'pear2\Pyrus\DER\Boolean',
        \pear2\Pyrus\DER\Enumerated::TAG => 'pear2\Pyrus\DER\Enumerated',
        \pear2\Pyrus\DER\GeneralizedTime::TAG => 'pear2\Pyrus\DER\GeneralizedTime',
        \pear2\Pyrus\DER\IA5String::TAG => 'pear2\Pyrus\DER\IA5String',
        \pear2\Pyrus\DER\Integer::TAG => 'pear2\Pyrus\DER\Integer',
        \pear2\Pyrus\DER\Null::TAG => 'pear2\Pyrus\DER\Null',
        \pear2\Pyrus\DER\NumericString::TAG => 'pear2\Pyrus\DER\NumericString',
        \pear2\Pyrus\DER\ObjectIdentifier::TAG => 'pear2\Pyrus\DER\ObjectIdentifier',
        \pear2\Pyrus\DER\OctetString::TAG => 'pear2\Pyrus\DER\OctetString',
        \pear2\Pyrus\DER\PrintableString::TAG => 'pear2\Pyrus\DER\PrintableString',
        \pear2\Pyrus\DER\Sequence::TAG => 'pear2\Pyrus\DER\Sequence',
        \pear2\Pyrus\DER\Set::TAG => 'pear2\Pyrus\DER\Set',
        \pear2\Pyrus\DER\UniversalString::TAG => 'pear2\Pyrus\DER\UniversalString',
        \pear2\Pyrus\DER\UTCTime::TAG => 'pear2\Pyrus\DER\UTCTime',
        \pear2\Pyrus\DER\UTF8String::TAG => 'pear2\Pyrus\DER\UTF8String',
        \pear2\Pyrus\DER\VisibleString::TAG => 'pear2\Pyrus\DER\VisibleString',
    );

    static function factory()
    {
        return new static;
    }

    function setOffset($offset)
    {
        $this->offset = $offset;
    }

    function setDepth($d)
    {
        $this->depth = $d;
    }

    function setSchema(\pear2\Pyrus\DER\Schema $schema)
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
        if ($name == '__offset') {
            return $this->offset;
        }
        if (!isset($this->schema)) {
            throw new \pear2\Pyrus\DER\Exception('To access objects, use ArrayAccess when schema is not set');
        }
        if (!isset($this->schema[$name])) {
            throw new \pear2\Pyrus\DER\Exception('schema has no element matching ' . $name .
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
            throw new \pear2\Pyrus\DER\Exception('To access objects, use ArrayAccess when schema is not set');
        }
        if (!isset($this->schema[$name])) {
            throw new \pear2\Pyrus\DER\Exception('schema has no element matching ' . $name .
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
        $obj = new \pear2\Pyrus\DER\BitString($string, $bits);
        $this->objs[] = $obj;
        return $this;
    }

    function _null()
    {
        $obj = new \pear2\Pyrus\DER\Null;
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
        $class = 'pear2\Pyrus\DER\\' . ucfirst($func);
        if (!class_exists($class, 1)) {
            throw new \pear2\Pyrus\DER\Exception('Unknown type ' . $func);
        }
        if (isset($args[0])) {
            $obj = new $class($args[0]);
        } else {
            $obj = new $class;
        }
        $this->objs[] = $obj;
        return $this;
    }

    function constructed(\pear2\Pyrus\DER $der)
    {
        $this->objs[] = $der;
        return $this;
    }

    function addMultiple($index, $obj)
    {
        if (!isset($this->objs[$index])) {
            // essentially use this as an array of values
            $this->objs[$index] = $obj;
        } elseif (get_class($this->objs[$index]) != 'pear2\Pyrus\DER') {
            $current = $this->objs[$index];
            $this->objs[$index] = new \pear2\Pyrus\DER;
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
        throw new \pear2\Pyrus\DER\Exception('offsetUnset not possible');
    }

    function offsetExists($var)
    {
        return isset($this->objs[$var]);
    }

    function type()
    {
        return substr(get_class($this), strlen('pear2\Pyrus\DER\\'));
    }

    function isType($tag)
    {
        return $this::TAG === $tag;
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

    function parseFromString($data, \pear2\Pyrus\DER $parent = null)
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
                $info = $this->schema->find($tag);
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
                        $tag = \pear2\Pyrus\DER\Sequence::TAG;
                    } else {
                        $tag = \pear2\Pyrus\DER\OctetString::TAG;
                    }
                }
                if (!isset($this->tagMap[$tag])) {
                    throw new \pear2\Pyrus\DER\Exception('Unknown tag: 0x' . dechex($tag));
                }
                $type = $this->tagMap[$tag];
                $parent[$index] = $obj = new $type;
            }

            if ($parent) {
                $obj->setOffset($parent->__offset + $location);
            } else {
                $obj->setOffset($location);
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
                        lcfirst(str_replace('pear2\Pyrus\DER\\', '', get_class($this))) .
                        "]: ";
                } else {
                    $ret = '';
                }
            } elseif (get_class($this) != 'pear2\Pyrus\DER') {
                $ret = str_repeat(' ', $this->depth);
                $ret .=
                    lcfirst(str_replace('pear2\Pyrus\DER\\', '', get_class($this))) .
                    ": ";
            } else {
                $ret = str_repeat(' ', $this->depth) . '(multiple): ';
            }

            foreach ($this->objs as $obj) {
                $ret .= "\n" . $obj;
            }
            if (isset($obj) && !($obj instanceof \pear2\Pyrus\DER\Constructed)) {
                $ret .= "\n";
            }
            if (isset($this->schema)) {
                $ret .= str_repeat(' ', $this->depth) .
                    "end " . $this->schema->name . "\n";
            } elseif (get_class($this) === 'pear2\Pyrus\DER') {
                $ret .= str_repeat(' ', $this->depth) . "end (multiple)\n";
            } else {
                $ret .= str_repeat(' ', $this->depth) .
                    "end " . lcfirst(str_replace('pear2\Pyrus\DER\\', '', get_class($this))) . "\n";
            }
            return $ret;
        }
        if (isset($this->schema)) {
            return str_repeat(' ', $this->depth) . $this->schema->name . ' [' .
                lcfirst(str_replace('pear2\Pyrus\DER\\', '', get_class($this))) . '] ' .
                '(' . $this->valueToString() . ')';
        }
        if (get_class($this) === 'pear2\Pyrus\DER') {
            return str_repeat(' ', $this->depth) . '[]';
        }
        return str_repeat(' ', $this->depth) .
            lcfirst(str_replace('pear2\Pyrus\DER\\', '', get_class($this))) .
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