<?php
/**
 * PEAR2_Pyrus_DER_OctetString
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
 * Represents a Distinguished Encoding Rule Octet String
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_DER_PrintableString extends PEAR2_Pyrus_DER_String
{
    const TAG = 0x13;

    function setValue($string)
    {
        if (!preg_match('/^[a-zA-Z0-9\'()+,\-\\.\/:=?]+\\z/', $string)) {
            throw new PEAR2_Pyrus_DER_Exception('Invalid Printable string value ' . $string .
                                                ', can only contain letters, digits, space and' .
                                                ' these punctuations: \' ( ) + , - . / : = ?');
        }
        $this->value = $string;
    }
}
