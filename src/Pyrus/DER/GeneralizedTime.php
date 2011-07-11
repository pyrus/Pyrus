<?php
/**
 * \Pyrus\DER\OctetString
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Represents a Distinguished Encoding Rule Octet String
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\DER;
class GeneralizedTime extends UTCTime
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
        $ret = \Pyrus\DER::parse($data, $location);
        $this->value = new \DateTime($this->value);
        return $ret;
    }
}
