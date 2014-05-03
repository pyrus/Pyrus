<?php
/**
 * \Pyrus\Task\Exception\NoAttributes
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
 * Exception class for Pyrus Tasks that are invalid because there are no attributes present
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
class NoAttributes extends \PEAR2\Exception
{
    function __construct($task, $file)
    {
        parent::__construct('task <' . $task . '> has no attributes in file ' . $file);
    }
}