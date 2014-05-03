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
class IA5String extends String
{
    const TAG = 0x16;

    function setValue($string)
    {
        if (preg_match('/[^\000-\177]/', $string)) {
            throw new Exception('Invalid IA5 String value, can only contain ASCII');
        }
        $this->value = $string;
    }
}
