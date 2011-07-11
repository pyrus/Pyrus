<?php
/**
 * \Pyrus\DER\IA5String
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
 * Represents a Distinguished Encoding Rule IA5String
 * 
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\DER;
abstract class String extends \Pyrus\DER
{
    protected $value;

    function __construct($string = '')
    {
        $this->setValue($string);
    }

    abstract function setValue($string);

    function serialize()
    {
        return $this->prependTLV($this->value, strlen($this->value));
    }
}
