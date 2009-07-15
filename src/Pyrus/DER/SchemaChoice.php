<?php
/**
 * \pear2\Pyrus\DER\SchemaChoice
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
 * Represents a Distinguished Encoding Rule IASN.1 schema Choice
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
class SchemaChoice extends \pear2\Pyrus\DER\Schema
{
    protected $options = array();

    function __construct(\pear2\Pyrus\DER\Schema $parent = null, $name = null, $tag = null)
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
                        $test->setClass('pear2\Pyrus\DER\Choice');
                    }
                    return $test;
                }
            } else {
                if ($obj->tag === $tag) {
                    return $obj;
                }
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
