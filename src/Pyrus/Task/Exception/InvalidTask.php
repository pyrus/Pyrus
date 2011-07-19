<?php
/**
 * \Pyrus\Task\Exception\InvalidTask
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
 * Exception class for Pyrus Tasks that are invalid for other reasons
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Task\Exception;
class InvalidTask extends \Pyrus\Task\Exception
{
    function __construct($task, $file, $reason)
    {
        parent::__construct('task <' . $task . '> in file ' . $file .
                    ' is invalid because of "' . $reason . '"');
    }
}