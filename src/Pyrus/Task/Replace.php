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
    var $phase = PEAR2_PYRUS_TASK_PACKAGEANDINSTALL;
    var $_replacements;

    /**
     * Validate the raw xml at parsing-time.
     * @param PEAR_PackageFile_v2
     * @param array raw, parsed xml
     * @param PEAR_Config
     * @static
     */
    static function validateXml($pkg, $xml, $config, $fileXml)
    {
        if (!isset($xml['attribs'])) {
            return array(PEAR2_PYRUS_TASK_ERROR_NOATTRIBS);
        }
        if (!isset($xml['attribs']['type'])) {
            return array(PEAR2_PYRUS_TASK_ERROR_MISSING_ATTRIB, 'type');
        }
        if (!isset($xml['attribs']['to'])) {
            return array(PEAR2_PYRUS_TASK_ERROR_MISSING_ATTRIB, 'to');
        }
        if (!isset($xml['attribs']['from'])) {
            return array(PEAR2_PYRUS_TASK_ERROR_MISSING_ATTRIB, 'from');
        }
        if ($xml['attribs']['type'] == 'pear-config') {
            if (!in_array($xml['attribs']['to'], $config->systemvars)) {
                return array(PEAR2_PYRUS_TASK_ERROR_WRONG_ATTRIB_VALUE, 'to', $xml['attribs']['to'],
                    $config->systemvars);
            }
        } elseif ($xml['attribs']['type'] == 'php-const') {
            if (defined($xml['attribs']['to'])) {
                return true;
            } else {
                return array(PEAR2_PYRUS_TASK_ERROR_WRONG_ATTRIB_VALUE, 'to', $xml['attribs']['to'],
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
                return array(PEAR2_PYRUS_TASK_ERROR_WRONG_ATTRIB_VALUE, 'to', $xml['attribs']['to'],
                    array('name', 'summary', 'channel', 'notes', 'extends', 'description',
                    'release_notes', 'license', 'release-license', 'license-uri',
                    'version', 'api-version', 'state', 'api-state', 'release_date',
                    'date', 'time'));
            }
        } else {
            return array(PEAR2_PYRUS_TASK_ERROR_WRONG_ATTRIB_VALUE, 'type', $xml['attribs']['type'],
                array('pear-config', 'package-info', 'php-const'));
        }
        return true;
    }

    /**
     * Initialize a task instance with the parameters
     * @param array raw, parsed xml
     * @param unused
     */
    function init($xml, $attribs)
    {
        $this->_replacements = isset($xml['attribs']) ? array($xml) : $xml;
    }

    /**
     * Do a package.xml 1.0 replacement, with additional package-info fields available
     *
     * See validateXml() source for the complete list of allowed fields
     * @param PEAR_PackageFile_v1|PEAR_PackageFile_v2
     * @param string file contents
     * @param string the eventual final file location (informational only)
     * @return string|false|PEAR_Error false to skip this file, PEAR_Error to fail
     *         (use $this->throwError), otherwise return the new contents
     */
    function startSession($pkg, $contents, $dest)
    {
        $subst_from = $subst_to = array();
        foreach ($this->_replacements as $a) {
            $a = $a['attribs'];
            $to = '';
            if ($a['type'] == 'pear-config') {
                if ($this->installphase == PEAR2_PYRUS_TASK_PACKAGE) {
                    return false;
                }
                if (false) {// $this->config->isDefinedLayer('ftp')) {
                    // try the remote config file first
                    $to = $this->config->{$a['to']};
                    if (is_null($to)) {
                        // then default to local
                        $to = $this->config->{$a['to']};
                    }
                } else {
                    $to = $this->config->{$a['to']};
                }
                if (is_null($to)) {
                    PEAR2_Pyrus_Log::log(0, "$dest: invalid pear-config replacement: $a[to]");
                    return false;
                }
            } elseif ($a['type'] == 'php-const') {
                if ($this->installphase == PEAR2_PYRUS_TASK_PACKAGE) {
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