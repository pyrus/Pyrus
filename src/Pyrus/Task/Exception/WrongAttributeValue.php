<?php
/**
 * \pear2\Pyrus\Task\Exception\WrongAttributeValue
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * Exception class for Pyrus Tasks that are invalid because the attribute value is invalid
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\Task\Exception;
class WrongAttributeValue extends \pear2\Exception
{
    function __construct($task, $attribute, $wrongvalue, $file, array $validvalues)
    {
        parent::__construct('task <' . $task . '> attribute "' . $attribute .
                    '" has the wrong value "' . $wrongvalue . '" '.
                    'in file ' . $file . ', expecting one of "' . implode (', ', $validvalues) . '"');
    }
}