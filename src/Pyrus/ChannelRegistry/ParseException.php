<?php
/**
 * \Pyrus\ChannelRegistry\ParseException
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
 * Base class for Exceptions when parsing channel registry.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\ChannelRegistry;
class ParseException extends \PEAR2\Exception
{
    public $why;
    public $params;

    function __construct($message, $why, $params = array())
    {
        $this->why = $why;
        $this->params = $params;
        parent::__construct($message);
    }
}