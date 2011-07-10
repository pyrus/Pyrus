<?php
/**
 * \Pyrus\DER\SchemaChoice
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Represents a Distinguished Encoding Rule IASN.1 schema Choice
 *
 * This is used to name components and to retrieve context-specific types
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\DER;
class SchemaChoice extends Schema
{
    protected $options = array();

    function __construct(Schema $parent = null, $name = null, $tag = null)
    {
        $this->parent = $parent;
        if ($name !== null) {
            $this->name = $name;
        }

        if ($tag !== null) {
            $this->tag = 0x80 | $tag;
        }
    }

    function findTag($tag)
    {
        if ($tag === $this->tag) {
            return $this;
        }

        foreach ($this->objs as $obj) {
            if ($obj instanceof self) {
                if ($test = $obj->findTag($tag)) {
                    if (!$test->class) {
                        $test->setClass('Pyrus\DER\Choice');
                    }

                    return $test;
                }
            } elseif ($obj->tag === $tag) {
                return $obj;
            }
        }

        return false;
    }

    function option($name, $type, $index = null)
    {
        if (null === $index) {
            $this->$type($name, count($this->objs));
        } else {
            $this->$type($name, $index);
        }

        return $this;
    }
}
