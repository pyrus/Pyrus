<?php
/**
 * <tasks:windowseol>
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
 * Implements the windows line endsings file task.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_Task_Windowseol extends PEAR2_Pyrus_Task_Common
{
    const TYPE = 'simple';
    const PHASE = PEAR2_Pyrus_Task_Common::PACKAGE;
    var $_replacements;

    /**
     * Validate the basic contents of a <qindowseol> tag
     * @param PEAR_Pyrus_IPackageFile
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     * @throws PEAR2_Pyrus_Task_Exception_InvalidTask
     */
    static function validateXml(PEAR2_Pyrus_IPackage $pkg, $xml, $fileXml, $file)
    {
        if ($xml != '') {
            throw new PEAR2_Pyrus_Task_Exception_InvalidTask('windowseol', $file, 'no attributes allowed');
        }
        return true;
    }

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param unused
     */
    function init($xml, $attribs, $lastversion)
    {
        parent::init($xml, $fileattribs, $lastversion);
    }

    /**
     * Replace all line endings with windows line endings
     *
     * See validateXml() source for the complete list of allowed fields
     * @param PEAR2_Pyrus_IPackage
     * @param resource open file pointer, set to the beginning of the file
     * @param string the eventual final file location (informational only)
     * @return string
     */
    function startSession(PEAR2_Pyrus_IPackage $pkg, $fp, $dest)
    {
        $contents = stream_get_contents($fp);
        PEAR2_Pyrus_Log::log(3, "replacing all line endings with \\r\\n in $dest");
        $contents = preg_replace("/\r\n|\n\r|\r|\n/", "\r\n", $contents);
        rewind($fp);
        fwrite($fp, $contents);
        return true;
    }
}
?>