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
 * @link      https://github.com/pyrus/Pyrus
 */

namespace Pyrus\DER;

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
class NumericString extends String
{
    const TAG = 0x12;

    function setValue($string)
    {
        if (!preg_match('/^[0-9 ]+\\z/', $string)) {
            throw new Exception('Invalid Numeric String value, can only contain digits and space');
        }
        $this->value = $string;
    }
}
