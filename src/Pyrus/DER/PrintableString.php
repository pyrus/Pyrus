<?php
/**
 * \pear2\Pyrus\DER\OctetString
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
namespace pear2\Pyrus\DER;
class PrintableString extends String
{
    const TAG = 0x13;

    function setValue($string)
    {
        if (strlen($string) && !preg_match('/^[a-zA-Z0-9\'()+,\-\\.\/:=?]+\\z/', $string)) {
            throw new Exception('Invalid Printable string value ' . $string .
                                ', can only contain letters, digits, space and' .
                                ' these punctuations: \' ( ) + , - . / : = ?');
        }
        $this->value = $string;
    }
}
