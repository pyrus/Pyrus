<?php
/**
 * <tasks:replace>
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
 * Implements the replace file task.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\Task;
class Replace extends \pear2\Pyrus\Task\Common
{
    const TYPE = 'simple';
    const PHASE = \pear2\Pyrus\Task\Common::PACKAGEANDINSTALL;
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
        $this->_replacements = isset($xml['attribs']) ? array($xml) : $xml;
    }

    /**
     * Validate the basic contents of a <replace> tag
     * @param PEAR_Pyrus_IPackageFile
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     * @throws \pear2\Pyrus\Task\Exception\InvalidTask
     */
    static function validateXml(\pear2\Pyrus\IPackage $pkg, $xml, $fileXml, $file)
    {
        if (!isset($xml['attribs'])) {
            throw new \pear2\Pyrus\Task\Exception\NoAttributes('replace', $file);
        }
        $errs = new \PEAR2_MultiErrors;
        foreach (array('type', 'to', 'from') as $attrib) {
            if (!isset($xml['attribs'][$attrib])) {
                $errs->E_ERROR[] =
                    new \pear2\Pyrus\Task\Exception\MissingAttribute('replace',
                                                                    $attrib, $file);
            }
        }
        if ($count = count($errs->E_ERROR)) {
            if ($count == 1) {
                throw $errs->E_ERROR[0];
            }
            throw new \pear2\Pyrus\Task\Exception('Invalid replace task, multiple missing attributes', $errs);
        }
        if ($xml['attribs']['type'] == 'pear-config') {
            $config = \pear2\Pyrus\Config::current();
            if (!in_array($xml['attribs']['to'], $config->systemvars)) {
                throw new \pear2\Pyrus\Task\Exception\WrongAttributeValue('replace',
                                                                         'to', $xml['attribs']['to'],
                                                                         $file,
                                                                         $config->systemvars);
            }
        } elseif ($xml['attribs']['type'] == 'php-const') {
            if (defined($xml['attribs']['to'])) {
                return true;
            } else {
                throw new \pear2\Pyrus\Task\Exception\WrongAttributeValue('replace',
                                                                         'to', $xml['attribs']['to'],
                                                                         $file,
                                                                         array('valid PHP constant'));
            }
        } elseif ($xml['attribs']['type'] == 'package-info') {
            if (in_array($xml['attribs']['to'],
                array('name', 'summary', 'channel', 'notes', 'extends', 'description',
                    'release_notes', 'license', 'release-license', 'license-uri',
                    'version', 'api-version', 'state', 'api-state', 'release_date',
                    'date', 'time'))) {
                return true;
            } else {
                throw new \pear2\Pyrus\Task\Exception\WrongAttributeValue('replace',
                                                                         'to', $xml['attribs']['to'],
                                                                         $file,
                    array('name', 'summary', 'channel', 'notes', 'extends', 'description',
                    'release_notes', 'license', 'release-license', 'license-uri',
                    'version', 'api-version', 'state', 'api-state', 'release_date',
                    'date', 'time'));
            }
        } else {
            throw new \pear2\Pyrus\Task\Exception\WrongAttributeValue('replace',
                                                                     'type', $xml['attribs']['type'],
                                                                     $file,
                                                                     array('pear-config',
                                                                           'package-info',
                                                                           'php-const'));
        }
        return true;
    }

    /**
     * Do a package.xml 1.0 replacement, with additional package-info fields available
     *
     * See validateXml() source for the complete list of allowed fields
     * @param \pear2\Pyrus\IPackage
     * @param resource open file pointer, set to the beginning of the file
     * @param string the eventual final file location (informational only)
     * @return string|false
     */
    function startSession($fp, $dest)
    {
        $contents = stream_get_contents($fp);
        $subst_from = $subst_to = array();
        foreach ($this->_replacements as $a) {
            $a = $a['attribs'];
            $to = '';
            if ($a['type'] == 'pear-config') {
                if ($this->installphase == \pear2\Pyrus\Task\Common::PACKAGE) {
                    return false;
                }
                $to = \pear2\Pyrus\Config::current()->{$a['to']};
                if (is_null($to)) {
                    \pear2\Pyrus\Logger::log(0, "$dest: invalid pear-config replacement: $a[to]");
                    return false;
                }
            } elseif ($a['type'] == 'php-const') {
                if ($this->installphase == \pear2\Pyrus\Task\Common::PACKAGE) {
                    return false;
                }
                if (defined($a['to'])) {
                    $to = constant($a['to']);
                } else {
                    \pear2\Pyrus\Logger::log(0, "$dest: invalid php-const replacement: $a[to]");
                    return false;
                }
            } else {
                if ($t = $this->pkg->{$a['to']}) {
                    if ($a['to'] == 'version') {
                        $t = $t['release'];
                    }
                    $to = $t;
                } else {
                    \pear2\Pyrus\Logger::log(0, "$dest: invalid package-info replacement: $a[to]");
                    return false;
                }
            }
            if (!is_null($to)) {
                $subst_from[] = $a['from'];
                $subst_to[] = $to;
            }
        }
        \pear2\Pyrus\Logger::log(3, "doing " . sizeof($subst_from) .
            " substitution(s) for $dest");
        if (sizeof($subst_from)) {
            $contents = str_replace($subst_from, $subst_to, $contents);
        }
        rewind($fp);
        ftruncate($fp, 0);
        fwrite($fp, $contents);
        return true;
    }

    function isPreProcessed()
    {
        if ($this->_replacements[0]['attribs']['type'] == 'package-info') {
            return true;
        }
        return false;
    }
}
?>