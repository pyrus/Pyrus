<?php
/**
 * \PEAR2\Pyrus\Task\Exception
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
 * Exception class for Pyrus Tasks
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\Task;
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