<?php
/**
 * \Pyrus\DER\UTF8String
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

/**
 * Represents a Distinguished Encoding Rule UTF8String
 *
 * No encoding check made, be sure the string really is UTF-8
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\DER;
class UTF8String extends String
{
    const TAG = 0x0C;

    function setValue($string)
    {
        $this->value = $string;
    }
}
