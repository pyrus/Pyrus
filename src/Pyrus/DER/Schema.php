<?php
/**
 * \Pyrus\DER\Schema
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
 * Represents a Distinguished Encoding Rule IASN.1 schema
 *
 * This is used to name components and to retrieve context-specific types
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class Schema extends \Pyrus\DER
{
    static protected $types = array();

    protected $name;
    protected $parent;
    protected $tag;
    protected $optional = false;
    protected $multiple = false;
    protected $class;
    protected $lastfind = false;

    function __construct(Schema $parent = null, $tag = 0, $type = '')
    {
        $this->parent = $parent;
        $this->tag = $tag;
        $this->class = $type;
    }

    function setOptional()
    {
        $this->optional = true;
        return $this;
    }

    function setMultiple()
    {
        $this->multiple = true;
        return $this;
    }

    function setName($name)
    {
        $this->name = $name;
    }

    function setTag($tag)
    {
        $this->tag = $tag;
    }

    function setClass($class)
    {
        $this->class = $class;
    }

    function multiple()
    {
        return $this->multiple;
    }

    function optional()
    {
        return $this->optional;
    }

    function __call($func, $args)
    {
        if ($func == 'choice') {
            if (isset($args[0])) {
                if (isset($args[1])) {
                    $obj = new SchemaChoice($this, $args[0], $args[1]);
                } else {
                    $obj = new SchemaChoice($this, $args[0]);
                }
                $this->objs[$args[0]] = $obj;
                return $obj;
            }

            return new SchemaChoice($this);
        }
        if (!isset($args[0])) {
            throw new Exception('Invalid schema, element must be named');
        }
        $name = $args[0];
        if ($func == 'any') {
            if (isset($args[1])) {
                $obj = new Schema($this, 0x80 | $args[1], 'any');
            } else {
                $obj = new Schema($this, 0, 'any');
            }
        } elseif (isset(self::$types[strtolower($func)])) {
            $obj = clone self::$types[strtolower($func)];
            $obj->setParent($this);
            if (isset($args[1])) {
                if ($obj->parentSchema()) {
                    $obj->setTag(0x80 | 0x20 | $args[1]);
                } else {
                    $obj->setTag(0x80 | $args[1]);
                }
            }
        } else {
            $class = 'Pyrus\DER\\' . ucfirst($func);
            if (!class_exists($class, 1)) {
                throw new Exception('Unknown type ' . $func . ' at ' . $this->path());
            }
            if (!isset($args[1])) {
                $tag = $class::TAG;
            } else {
                $tag = $args[1] | 0x80;
                if (strtolower($func) == 'set' || strtolower($func) == 'sequence') {
                    $tag |= 0x20;
                }
            }
            $obj = new Schema($this, $tag, $class);
        }
        $this->objs[$name] = $obj;
        $obj->setName($name);
        if ($obj->parentSchema() && !isset(self::$types[strtolower($func)])) {
            return $obj;
        } else {
            return $this;
        }
    }

    function __clone()
    {
        foreach ($this->objs as $i => $obj) {
            $this->objs[$i] = clone $obj;
            $obj->setParent($this);
        }
    }

    function parentSchema()
    {
        if ($this instanceof SchemaChoice) {
            return true;
        }
        if ($this->class === 'Pyrus\DER\Sequence') {
            return true;
        }
        if ($this->class === 'Pyrus\DER\Set') {
            return true;
        }
        return false;
    }

    function setParent(Schema $parent)
    {
        $this->parent = $parent;
    }

    function end()
    {
        return $this->parent;
    }

    static function addType($name, Schema $schema)
    {
        self::$types[strtolower($name)] = $schema;
    }

    static function types()
    {
        return self::$types;
    }

    function __get($var)
    {
        if ($var === 'types') {
            return self::$types;
        }
        if ($var == 'name') {
            return $this->name;
        }
        if ($var == 'tag') {
            return $this->tag;
        }
        if ($var == 'type') {
            return $this->class;
        }
        if (isset($this->objs[$var])) {
            return $this->objs[$var];
        }
        throw new Exception('Unknown schema element ' . $var);
    }


    function findTag($tag)
    {
        if ($this->tag === $tag) {
            return $this;
        }
        return false;
    }

    function resetLastFind()
    {
        $this->lastfind = false;
    }

    function find($tag)
    {
        foreach ($this->objs as $index => $obj) {
            if ($this->lastfind && $index != $this->lastfind) {
                continue;
            }
            if ($this->lastfind) {
                if ($obj->multiple() && $this->lastfind == $index) {
                    if ($test = $obj->findTag($tag)) {
                        return $test;
                    }
                }
                $this->lastfind = false;
                continue;
            }
            if ($obj->type === 'any') {
                if (($tag & 0x80) === 0x80) {
                    // context-sensitive tag, do best guess
                    if (($tag & 0x20) == 0x20) {
                        $tag = Sequence::TAG;
                    } else {
                        $tag = OctetString::TAG;
                    }
                }
                if (!isset($this->tagMap[$tag])) {
                    throw new Exception('Unknown tag: ' . dechex($tag) . ' at ' . $this->path());
                }
                $type = $this->tagMap[$tag];
                $ret = new Schema($this->parent, $tag, $type);
                $ret->setName($obj->name);
                $this->lastfind = $index;
                return $ret;
            }
            if ($test = $obj->findTag($tag)) {
                $this->lastfind = $index;
                if ($test->name != $index) {
                    $test = clone $test;
                    $test->setName($index);
                }
                return $test;
            }
            if (!$obj->optional()) {
                if (isset($this->tagMap[$tag])) {
                    $tag = '"' . str_replace('Pyrus\DER\\', '', $this->tagMap[$tag]) .
                        '" (0x' . dechex($tag) . ')';
                } else {
                    $tag = dechex($tag);
                }
                throw new Exception('Invalid DER document, required tag ' .
                                                    $index . ' not found, instead requested ' .
                                                    'tag value ' . $tag . ' at ' .
                                                    $this->path());
            }
        }
        if (isset($this->tagMap[$tag])) {
            $tag = '"' . str_replace('Pyrus\DER\\', '', $this->tagMap[$tag]) . '" (0x' . dechex($tag) . ')';
        } else {
            $tag = dechex($tag);
        }
        throw new Exception('Invalid DER document, no matching elements for tag ' . $tag .
                                            ' at ' . $this->path());
    }

    function path()
    {
        if ($this->parent && $this->parent->path()) {
            return $this->parent->path() . '->' . $this->name;
        }
        return $this->name;
    }
}
