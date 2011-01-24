<?php
/**
 * \PEAR2\Pyrus\DER\UTCTime
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Represents a Distinguished Encoding Rule UTC Time
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\DER;
class UTCTime extends \PEAR2\Pyrus\DER
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
