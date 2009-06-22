<?php
/**
 * PEAR2_Pyrus_DER_SchemaChoice
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
class PEAR2_Pyrus_DER_SchemaChoice extends PEAR2_Pyrus_DER_Schema
{
    protected $options = array();

    function __construct(PEAR2_Pyrus_DER_Schema $parent = null, $name = null)
    {
        $this->parent = $parent;
        if ($name !== null) {
            $this->name = $name;
        }
    }

    function option($name, $type)
    {
        $this->$type($name, count($this->objs));
        return $this;
    }
}
