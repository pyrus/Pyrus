<?php
/**
 * \pear2\Pyrus\DER\Boolean
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
 * Represents a Distinguished Encoding Rule boolean value
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\DER;
class Boolean extends \pear2\Pyrus\DER
{
    const TAG = 0x01;
    protected $value = false;

    function __construct($value = false)
    {
        $this->value = $value;
    }

    function setValue($value)
    {
        $this->value = (bool) $value;
    }

    function serialize()
    {
        $bool = $this->value ? chr(0xFF) : chr(0x00);
        return $this->prependTLV($bool, 1);
    }

    function parse($data, $location)
    {
        $ret = parent::parse($data, $location);
        $this->value = (bool) ord($this->value);
        return $ret;
    }

    function valueToString()
    {
        return $this->value ? 'TRUE' : 'FALSE';
    }
}
