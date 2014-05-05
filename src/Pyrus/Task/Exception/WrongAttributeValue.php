<?php
/**
 * \Pyrus\Task\Exception\WrongAttributeValue
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

namespace Pyrus\Task\Exception;

/**
 * Exception class for Pyrus Tasks that are invalid because the attribute value is invalid
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class WrongAttributeValue extends \PEAR2\Exception
{
    function __construct($task, $attribute, $wrongvalue, $file, array $validvalues)
    {
        parent::__construct('task <' . $task . '> attribute "' . $attribute .
                    '" has the wrong value "' . $wrongvalue . '" '.
                    'in file ' . $file . ', expecting one of "' . implode (', ', $validvalues) . '"');
    }
}