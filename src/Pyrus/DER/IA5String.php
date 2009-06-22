<?php
/**
 * PEAR2_Pyrus_DER_IA5String
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
 * Represents a Distinguished Encoding Rule IA5String
 * 
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_DER_IA5String extends PEAR2_Pyrus_DER_String
{
    const TAG = 0x16;

    function setValue($string)
    {
        if (preg_match('/[^\000-\177]/', $string)) {
            throw new PEAR2_Pyrus_DER_Exception('Invalid IA5 String value, can only contain ASCII');
        }
        $this->value = $string;
    }
}
