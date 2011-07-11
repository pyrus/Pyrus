<?php
/**
 * \Pyrus\Task\Exception
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
 * Exception class for Pyrus Tasks
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Task;
class Exception extends \PEAR2\Exception
{
    /**#@+
     * Error codes for task validation routines
     */
    const NOATTRIBS = 1;
    const MISSING_ATTRIB = 2;
    const WRONG_ATTRIB_VALUE = 3;
    const INVALID = 4;
    /**#@-*/
}