<?php
/**
 * PEAR2_Pyrus_Validate_Exception
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
 * Exception class for Validate
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Validate_Exception extends PEAR2_Exception
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