<?php
/**
 * PEAR2_Pyrus_DER_OctetString
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
 * Represents a Distinguished Encoding Rule Octet String
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_DER_GeneralizedTime extends PEAR2_Pyrus_DER_UTCTime
{
    const TAG = 0x18;

    function serialize()
    {
        $value = $this->value->format('YmdHis');
        $value .= 'Z';

        return $this->prependTLV($value, strlen($value));
    }

    function valueToString()
    {
        if ($this->value instanceof DateTime) {
            return $this->value->format('YmdHis') . 'Z';
        } else {
            return '<Uninitialized GeneralizedTime>';
        }
    }

    function parse($data, $location)
    {
        $ret = PEAR2_Pyrus_DER::parse($data, $location);
        $this->value = new DateTime($this->value);
        return $ret;
    }
}
