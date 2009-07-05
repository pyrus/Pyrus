<?php
/**
 * \pear2\Pyrus\DER\Schema
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
namespace pear2\Pyrus\DER;
class Schema extends \pear2\Pyrus\DER
{
    static protected $types = array();

    protected $name;
    protected $parent;
    protected $tag;
    protected $optional = false;
    protected $multiple = false;
    protected $class;
    protected $lastfind = false;

    function __construct(\pear2\Pyrus\DER\Schema $parent = null, $tag = 0, $type = '')
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
                return new \pear2\Pyrus\DER\SchemaChoice($this, $args[0]);
            } else {
                return new \pear2\Pyrus\DER\SchemaChoice($this);
            }
        }
        if (!isset($args[0])) {
            throw new \pear2\Pyrus\DER\Exception('Invalid schema, element must be named');
        }
        $name = $args[0];
        if ($func == 'any') {
            if (isset($args[1])) {
                $obj = new \pear2\Pyrus\DER\Schema($this, 0x80 | $args[1], 'any');
            } else {
                $obj = new \pear2\Pyrus\DER\Schema($this, 0, 'any');
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
            $class = 'pear2\Pyrus\DER\\' . ucfirst($func);
            if (!class_exists($class, 1)) {
                throw new \pear2\Pyrus\DER\Exception('Unknown type ' . $func .
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
            $obj = new \pear2\Pyrus\DER\Schema($this, $tag, $class);
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
        if ($this instanceof \pear2\Pyrus\DER\SchemaChoice) {
            return true;
        }
        if ($this->class === 'pear2\Pyrus\DER\Sequence') {
            return true;
        }
        if ($this->class === 'pear2\Pyrus\DER\Set') {
            return true;
        }
        return false;
    }

    function setParent(\pear2\Pyrus\DER\Schema $parent)
    {
        $this->parent = $parent;
    }

    function end()
    {
        return $this->parent;
    }

    static function addType($name, \pear2\Pyrus\DER\Schema $schema)
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
        throw new \pear2\Pyrus\DER\Exception('Unknown schema element ' . $var);
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
                        $tag = \pear2\Pyrus\DER\Sequence::TAG;
                    } else {
                        $tag = \pear2\Pyrus\DER\OctetString::TAG;
                    }
                }
                if (!isset($this->tagMap[$tag])) {
                    throw new \pear2\Pyrus\DER\Exception('Unknown tag: ' . dechex($tag) . ' at ' .
                                                        $this->path());
                }
                $type = $this->tagMap[$tag];
                $ret = new \pear2\Pyrus\DER\Schema($this->parent, $tag, $type);
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
                    $tag = '"' . str_replace('pear2\Pyrus\DER\\', '', $this->tagMap[$tag]) .
                        '" (0x' . dechex($tag) . ')';
                } else {
                    $tag = dechex($tag);
                }
                throw new \pear2\Pyrus\DER\Exception('Invalid DER document, required tag ' .
                                                    $index . ' not found, instead requested ' .
                                                    'tag value ' . $tag . ' at ' .
                                                    $this->path());
            }
        }
        if (isset($this->tagMap[$tag])) {
            $tag = '"' . str_replace('pear2\Pyrus\DER\\', '', $this->tagMap[$tag]) . '" (0x' . dechex($tag) . ')';
        } else {
            $tag = dechex($tag);
        }
        throw new \pear2\Pyrus\DER\Exception('Invalid DER document, no matching elements for tag ' . $tag .
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
