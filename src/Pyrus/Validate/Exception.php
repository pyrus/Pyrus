<?php
/**
 * \Pyrus\Validate\Exception
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
 * Exception class for Validate
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Validate;
class Exception extends \PEAR2\Exception
{
    /**
     * package.xml field that failed channel-specific validation
     *
     * @var string
     */
    public $field;
    /**
     * The reason that validation failed
     *
     * @var string
     */
    public $reason;
    /**
     * Set up message/field combination for package.xml validation
     *
     * @param string $msg
     * @param string $field
     */
    public function __construct($msg, $field)
    {
        $this->reason = $msg;
        $msg = 'Channel validator error: field "' . $field . '" - ' .
                    $msg;
        parent::__construct($msg);
        $this->field = $field;
    }
}