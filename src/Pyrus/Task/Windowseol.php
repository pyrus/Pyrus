<?php
/**
 * <tasks:windowseol>
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
 * Implements the windows line endsings file task.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\Task;
class Windowseol extends \Pyrus\Task\Common
{
    const TYPE = 'simple';
    const PHASE = Common::PACKAGEANDINSTALL;
    var $_replacements;

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param array attributes from the <file> tag containing this task
     * @param string|null last installed version of this package
     */
    function __construct($pkg, $phase, $xml, $attribs, $lastversion)
    {
        parent::__construct($pkg, $phase, $xml, $attribs, $lastversion);
    }

    /**
     * Validate the basic contents of a <windowseol> tag
     *
     * @param PEAR_Pyrus_PackageFileInterface
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     *
     * @throws \Pyrus\Task\Exception\InvalidTask
     */
    static function validateXml(\Pyrus\PackageInterface $pkg, $xml, $fileXml, $file)
    {
        if (is_array($xml) && count($xml) || $xml !== '') {
            throw new Exception\InvalidTask('windowseol', $file, 'no attributes allowed');
        }
        return true;
    }

    /**
     * Replace all line endings with windows line endings
     *
     * See validateXml() source for the complete list of allowed fields
     * @param \Pyrus\PackageInterface
     * @param resource open file pointer, set to the beginning of the file
     * @param string the eventual final file location (informational only)
     * @return string
     */
    function startSession($fp, $dest)
    {
        $contents = stream_get_contents($fp);
        \Pyrus\Logger::log(3, "replacing all line endings with \\r\\n in $dest");
        $contents = preg_replace("/\r\n|\n\r|\r|\n/", "\r\n", $contents);
        rewind($fp);
        ftruncate($fp, 0);
        fwrite($fp, $contents);
        return true;
    }

    function isPreProcessed()
    {
        return true;
    }
}