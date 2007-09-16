<?php
/**
 * package.xml parsing class, package.xml version 2.0
 *
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id: v2.php,v 1.2 2007/06/03 18:08:49 cellog Exp $
 * @link       http://pear.php.net/package/PEAR
 * @since      File available since Release 1.4.0a1
 */
/**
 * Parser for package.xml version 2.0
 * @category   pear
 * @package    PEAR
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  1997-2006 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @PEAR-VER@
 * @link       http://pear.php.net/package/PEAR
 * @since      Class available since Release 1.4.0a1
 */
class PEAR2_Pyrus_PackageFile_Parser_v2 extends PEAR2_Pyrus_XMLParser
{
    private $_inContents = false;
    private $_path = '';
    private $_files = array();
    private $_lastDepth = 0;
    private $_inFile = 0;
    private $_curFile;
    /**
     * Unindent given string
     *
     * @param string $str The string that has to be unindented.
     * @return string
     * @access private
     */
    function _unIndent($str)
    {
        // remove leading newlines
        $str = preg_replace('/^[\r\n]+/', '', $str);
        // find whitespace at the beginning of the first line
        $indent_len = strspn($str, " \t");
        $indent = substr($str, 0, $indent_len);
        $data = '';
        // remove the same amount of whitespace from following lines
        foreach (explode("\n", $str) as $line) {
            if (substr($line, 0, $indent_len) == $indent) {
                $data .= substr($line, $indent_len) . "\n";
            }
        }
        return $data;
    }

    /**
     * post-process data
     *
     * @param string $data
     * @param string $element element name
     */
    function postProcess($data, $element)
    {
        if ($element == 'notes') {
            return trim($this->_unIndent($data));
        }
        return trim($data);
    }

    /**
     * @param string
     * @param string file name of the package.xml
     * @param string|false name of the archive this package.xml came from, if any
     * @param string class name to instantiate and return.  This must be PEAR_PackageFile_v2 or
     *               a subclass
     * @return PEAR2_Pyrus_PackageFile_v2
     */
    function parse($data, $file, $class = 'PEAR2_Pyrus_PackageFile_v2')
    {
        $this->_inContents = false;
        $this->_path = '';
        $this->_files = array();
        $this->_lastDepth = 0;
        $this->_inFile = 0;
        $ret = new $class;
        $ret->setConfig(PEAR2_Pyrus_Config::current());
        if (isset($this->_logger)) {
            $ret->setLogger($this->_logger);
        }
        
        if (preg_match('/<package[^>]+version="2.1"/', $data)) {
            $schema = realpath(dirname(dirname(dirname(dirname(dirname(__FILE__))))) .
                '/data/pear.php.net/PEAR2_Pyrus/package-2.1.xsd');
            // for running out of cvs
            if (!$schema) {
                $schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/data/package-2.1.xsd';
            }
        } else {
            $schema = realpath(dirname(dirname(dirname(dirname(dirname(__FILE__))))) .
                '/data/pear.php.net/PEAR2_Pyrus/package-2.0.xsd');
            // for running out of cvs
            if (!$schema) {
                $schema = dirname(dirname(dirname(dirname(__FILE__)))) . '/data/package-2.0.xsd';
            }
        }
        try {
            $ret->fromArray(parent::parseString($data, $schema));
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_PackageFile_Exception('Invalid package.xml', $e);
        }
        $ret->setFileList($this->_files);
        $ret->setPackagefile($file);
        return $ret;
    }

    protected function mergeTag(&$arr, $tag, $attr, $name, $depth)
    {
        parent::mergeTag($arr, $tag, $attr, $name, $depth);
        if ($this->_inContents) {
            while ($this->_lastDepth > $depth) {
                --$this->_lastDepth;
                $this->_path = dirname($this->_path);
            }
            if ($this->_inFile) {
                if ($depth < $this->_inFile) {
                    $this->_inFile = 0;
                }
            }
            if ($name === 'dir') {
                $this->_lastDepth = $depth;
                $path = $attr['name'];
                if ($path === '/') {
                    $path = '';
                } else {
                    $path .= '/';
                }
                $this->_path .= $path;
            } elseif ($name === 'file') {
                if (isset($arr[$name][0])) {
                    $this->_files[$this->_path . '/' . $attr['name']] =
                        $arr[$name][count($arr[$name]) - 1];
                } else {
                    $this->_files[$this->_path . '/' . $attr['name']] = $arr[$name];
                }
                $this->_curFile = $this->_path . '/' . $attr['name'];
                $this->_inFile = $depth;
            } elseif ($this->_inFile) {
                // add tasks
                $this->_files[$this->_curFile][$name] = $arr[$name];
            }
        } elseif ($name === 'contents') {
            $this->_inContents = true;
        }
    }
}
?>