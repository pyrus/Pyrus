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
class PEAR2_Pyrus_Task_Replace extends PEAR2_Pyrus_Task_Common
{
    var $type = 'simple';
    var $phase = PEAR2_Pyrus_Task_Common::PACKAGEANDINSTALL;
    var $_replacements;

    /**
     * Validate the basic contents of a <unixeol> tag
     * @param PEAR_Pyrus_IPackageFile
     * @param array
     * @param array the entire parsed <file> tag
     * @param string the filename of the package.xml
     * @throws PEAR2_Pyrus_Task_Exception_InvalidTask
     */
    static function validateXml(PEAR2_Pyrus_IPackage $pkg, $xml, $fileXml, $file)
    {
        if (!isset($xml['attribs'])) {
            throw new PEAR2_Pyrus_Task_Exception_NoAttributes('replace', $file);
        }
        $errs = new PEAR2_MultiErrors;
        foreach (array('type', 'to', 'from') as $attrib) {
            if (!isset($xml['attribs']['type'])) {
                $errs->E_ERROR[] =
                    new PEAR2_Pyrus_Task_Exception_MissingAttribute('replace',
                                                                    $attrib, $file);
            }
        }
        if ($count = count($errs->E_ERROR)) {
            if ($count == 1) {
                throw $errs->E_ERROR[0];
            }
            throw new PEAR2_Pyrus_Task_Exception('Invalid replace task, multiple missing attributes', $errs);
        }
        if ($xml['attribs']['type'] == 'pear-config') {
            if (!in_array($xml['attribs']['to'], $config->systemvars)) {
                throw new PEAR2_Pyrus_Task_Exception_WrongAttributeValue('replace',
                                                                         'to', $xml['attribs']['to'],
                                                                         $file,
                                                                         $config->systemvars);
            }
        } elseif ($xml['attribs']['type'] == 'php-const') {
            if (defined($xml['attribs']['to'])) {
                return true;
            } else {
                throw new PEAR2_Pyrus_Task_Exception_WrongAttributeValue('replace',
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
                throw new PEAR2_Pyrus_Task_Exception_WrongAttributeValue('replace',
                                                                         'to', $xml['attribs']['to'],
                                                                         $file,
                    array('name', 'summary', 'channel', 'notes', 'extends', 'description',
                    'release_notes', 'license', 'release-license', 'license-uri',
                    'version', 'api-version', 'state', 'api-state', 'release_date',
                    'date', 'time'));
            }
        } else {
            throw new PEAR2_Pyrus_Task_Exception_WrongAttributeValue('replace',
                                                                     'to', $xml['attribs']['to'],
                                                                     $file,
                                                                     array('pear-config',
                                                                           'package-info',
                                                                           'php-const'));
        }
        return true;
    }

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param unused
     * @param unused
     */
    function init($xml, $attribs, $lastVersion)
    {
        $this->_replacements = isset($xml['attribs']) ? array($xml) : $xml;
        parent::init($xml, $attribs, $lastVersion);
    }

    /**
     * Do a package.xml 1.0 replacement, with additional package-info fields available
     *
     * See validateXml() source for the complete list of allowed fields
     * @param PEAR2_Pyrus_IPackage
     * @param string file contents
     * @param string the eventual final file location (informational only)
     * @return string|false
     */
    function startSession(PEAR2_Pyrus_IPackage $pkg, $contents, $dest)
    {
        $subst_from = $subst_to = array();
        foreach ($this->_replacements as $a) {
            $a = $a['attribs'];
            $to = '';
            if ($a['type'] == 'pear-config') {
                if ($this->installphase == PEAR2_Pyrus_Task_Common::PACKAGE) {
                    return false;
                }
                $to = $this->config->{$a['to']};
                if (is_null($to)) {
                    PEAR2_Pyrus_Log::log(0, "$dest: invalid pear-config replacement: $a[to]");
                    return false;
                }
            } elseif ($a['type'] == 'php-const') {
                if ($this->installphase == PEAR2_Pyrus_Task_Common::PACKAGE) {
                    return false;
                }
                if (defined($a['to'])) {
                    $to = constant($a['to']);
                } else {
                    PEAR2_Pyrus_Log::log(0, "$dest: invalid php-const replacement: $a[to]");
                    return false;
                }
            } else {
                if ($t = $pkg->{$a['to']}) {
                    if ($a['to'] == 'version') {
                        $t = $t['release'];
                    }
                    $to = $t;
                } else {
                    PEAR2_Pyrus_Log::log(0, "$dest: invalid package-info replacement: $a[to]");
                    return false;
                }
            }
            if (!is_null($to)) {
                $subst_from[] = $a['from'];
                $subst_to[] = $to;
            }
        }
        PEAR2_Pyrus_Log::log(3, "doing " . sizeof($subst_from) .
            " substitution(s) for $dest");
        if (sizeof($subst_from)) {
            $contents = str_replace($subst_from, $subst_to, $contents);
        }
        return $contents;
    }
}
?>