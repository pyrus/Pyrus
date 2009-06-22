<?php
/**
 * PEAR2_Pyrus_DER_Schema
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
 * Represents a Distinguished Encoding Rule IASN.1 schema
 *
 * This is used to name components and to retrieve context-specific types
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_DER_Schema extends PEAR2_Pyrus_DER
{
    static protected $types = array();

    protected $name;
    protected $parent;
    protected $tag;
    protected $optional = false;
    protected $multiple = false;
    protected $class;
    protected $lastfind = false;

    function __construct(PEAR2_Pyrus_DER_Schema $parent = null, $tag = 0, $type = '')
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
                return new PEAR2_Pyrus_DER_SchemaChoice($this, $args[0]);
            } else {
                return new PEAR2_Pyrus_DER_SchemaChoice($this);
            }
        }
        if (!isset($args[0])) {
            throw new PEAR2_Pyrus_DER_Exception('Invalid schema, element must be named');
        }
        $name = $args[0];
        if ($func == 'any') {
            if (isset($args[1])) {
                $obj = new PEAR2_Pyrus_DER_Schema($this, 0x80 | $args[1], 'any');
            } else {
                $obj = new PEAR2_Pyrus_DER_Schema($this, 0, 'any');
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
            $class = 'PEAR2_Pyrus_DER_' . ucfirst($func);
            if (!class_exists($class, 1)) {
                throw new PEAR2_Pyrus_DER_Exception('Unknown type ' . $func .
                                                    ' at ' . $this->path());
            }
            if (!isset($args[1])) {
                $tag = $class::TAG;
            } else {
                $tag = $args[1] | 0x80;
                if (strtolower($func) == 'set' || strtolower($func) == 'sequence') {
                    $tag |= 0x20;
                }
            }
            $obj = new PEAR2_Pyrus_DER_Schema($this, $tag, $class);
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
        if ($this instanceof PEAR2_Pyrus_DER_SchemaChoice) {
            return true;
        }
        if ($this->class === 'PEAR2_Pyrus_DER_Sequence') {
            return true;
        }
        if ($this->class === 'PEAR2_Pyrus_DER_Set') {
            return true;
        }
        return false;
    }

    function setParent(PEAR2_Pyrus_DER_Schema $parent)
    {
        $this->parent = $parent;
    }

    function end()
    {
        return $this->parent;
    }

    static function addType($name, PEAR2_Pyrus_DER_Schema $schema)
    {
        self::$types[strtolower($name)] = $schema;
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
        throw new PEAR2_Pyrus_DER_Exception('Unknown schema element ' . $var);
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
                        $tag = PEAR2_Pyrus_DER_Sequence::TAG;
                    } else {
                        $tag = PEAR2_Pyrus_DER_OctetString::TAG;
                    }
                }
                if (!isset($this->tagMap[$tag])) {
                    throw new PEAR2_Pyrus_DER_Exception('Unknown tag: ' . dechex($tag) . ' at ' .
                                                        $this->path());
                }
                $type = $this->tagMap[$tag];
                $ret = new PEAR2_Pyrus_DER_Schema($this->parent, $tag, $type);
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
                    $tag = '"' . str_replace('PEAR2_Pyrus_DER_', '', $this->tagMap[$tag]) .
                        '" (0x' . dechex($tag) . ')';
                } else {
                    $tag = dechex($tag);
                }
                throw new PEAR2_Pyrus_DER_Exception('Invalid DER document, required tag ' .
                                                    $index . ' not found, instead requested ' .
                                                    'tag value ' . $tag . ' at ' .
                                                    $this->path());
            }
        }
        if (isset($this->tagMap[$tag])) {
            $tag = '"' . str_replace('PEAR2_Pyrus_DER_', '', $this->tagMap[$tag]) . '" (0x' . dechex($tag) . ')';
        } else {
            $tag = dechex($tag);
        }
        throw new PEAR2_Pyrus_DER_Exception('Invalid DER document, no matching elements for tag ' . $tag .
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
