<?php
/**
 * \Pyrus\DER\UTCTime
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
 * Represents a Distinguished Encoding Rule UTC Time
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class UTCTime extends \Pyrus\DER
{
    const TAG = 0x17;
    protected $value;

    function __construct(\DateTime $date = null)
    {
        $this->setValue($date);
    }

    function setValue(\DateTime $date = null)
    {
        if ($date === null) {
            $date = date_create();
        }
        $date->setTimezone(new \DateTimeZone('UTC'));
        $this->value = $date;
    }

    function serialize()
    {
        $value = $this->value->format('ymdHis');
        $value .= 'Z';

        return $this->prependTLV($value, strlen($value));
    }

    function parse($data, $location)
    {
        $ret = parent::parse($data, $location);
        // Y2K issues
        if ($this->value[0] < 5) {
            $this->value = '20' . $this->value;
        } else {
            $this->value = '19' . $this->value;
        }
        $this->value = new \DateTime($this->value);
        return $ret;
    }

    function valueToString()
    {
        if ($this->value instanceof \DateTime) {
            return $this->value->format('ymdHis') . 'Z';
        }

        return '<Uninitialized UTCTime>';
    }
}
