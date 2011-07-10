<?php
/**
 * \Pyrus\Task\Exception\MissingAttribute
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Exception class for Pyrus Tasks that are invalid due to a missing attribute
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace Pyrus\Task\Exception;
class MissingAttribute extends \PEAR2\Exception
{
    function __construct($task, $attribute, $file)
    {
        parent::__construct('task <' . $task . '> is missing attribute "' . $attribute .
                    '" in file ' . $file);
    }
}