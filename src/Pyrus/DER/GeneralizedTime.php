<?php
/**
 * \pear2\Pyrus\DER\OctetString
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
namespace pear2\Pyrus\DER;
class GeneralizedTime extends \pear2\Pyrus\DER\UTCTime
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
        return $this->value->format('YmdHis') . 'Z';
    }

    function parse($data, $location)
    {
        $ret = \pear2\Pyrus\DER::parse($data, $location);
        $this->value = new \DateTime($this->value);
        return $ret;
    }
}
