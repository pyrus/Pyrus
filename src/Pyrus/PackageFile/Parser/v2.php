<?php
/**
 * package.xml parsing class, package.xml version 2.0
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
 * Parser for package.xml version 2.0
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus\PackageFile\Parser;
class v2 extends \pear2\Pyrus\XMLParser
{
    private $_inContents = false;
    private $_path = '';
    private $_files = array();
    /**
     * Mapping of directories within package.xml and their baseinstalldir settings
     * @var array
     */
    private $_baseinstalldirs = array();
    private $_lastDepth = 0;
    private $_lastFileDepth = 0;
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
     * @return \pear2\Pyrus\PackageFile\v2
     */
    function parse($data, $file = false, $class = 'pear2\Pyrus\PackageFile\v2', $state = \pear2\Pyrus\Validate::NORMAL)
    {
        $this->_inContents = false;
        $this->_path = '';
        $this->_files = array();
        $this->_lastDepth = $this->_lastFileDepth = 0;
        $this->_inFile = 0;
        $ret = new $class;
        if (!$ret instanceof \pear2\Pyrus\PackageFile\v2) {
            throw new \pear2\Pyrus\PackageFile\Exception('Class ' . $class .
                ' passed to parse() must be a child class of \pear2\Pyrus\PackageFile\v2');
        }

        if (preg_match('/<package[^>]+version="2.1"/', $data)) {
            $schema = \pear2\Pyrus\Main::getDataPath() . '/package-2.1.xsd';
            // for running out of cvs
            if (!file_exists($schema)) {
                $schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/package-2.1.xsd';
            }
        } elseif (preg_match('/<package[^>]+version="2.0"/', $data)) {
            $schema = \pear2\Pyrus\Main::getDataPath() . '/package-2.0.xsd';
            // for running out of cvs
            if (!file_exists($schema)) {
                $schema = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/data/package-2.0.xsd';
            }
        } else {
            throw new \pear2\Pyrus\PackageFile\Exception('Cannot process package.xml version 1.0', -3);
        }
        try {
            $ret->fromArray(parent::parseString($data, $schema));
        } catch (\Exception $e) {
            throw new \pear2\Pyrus\PackageFile\Exception('Invalid package.xml', $e);
        }
        $ret->setFileList($this->_files);
        $ret->setBaseInstallDirs($this->_baseinstalldirs);
        $ret->setPackagefile($file);
        return $ret;
    }

    /**
     * Merge a tag into the array
     * @see \pear2\Pyrus\XMLParser::mergeTag()
     *
     * @param array  $arr     The array representation of the XML
     * @param string $tag     The tag name
     * @param array  $attribs Associative array of attributes for this tag
     * @param string $name    The tag name
     * @param int    $depth   The current depth within the XML document
     *
     * @return array
     */
    protected function mergeTag($arr, $tag, $attribs, $name, $depth)
    {
        $arr = parent::mergeTag($arr, $tag, $attribs, $name, $depth);
        if ($this->_inContents) {
            if ($this->_inFile) {
                if ($depth < $this->_inFile) {
                    $this->_inFile = 0;
                }
            }
            if ($name === 'dir') {
                while ($this->_lastDepth >= $depth) {
                    $this->_path = dirname($this->_path);
                    if ($this->_path == '.') {
                        $this->_path = '';
                    } else {
                        $this->_path .= '/';
                    }
                    $this->_lastDepth--;
                }
                $this->_lastDepth = $depth;
                $this->_lastFileDepth = $depth + 1;
                $origpath = $path = $attribs['name'];
                if ($path === '/') {
                    $path = '';
                } else {
                    $path .= '/';
                }
                $this->_path .= $path;
                if (isset($attribs['baseinstalldir'])) {
                    $this->_baseinstalldirs[$origpath] = $attribs['baseinstalldir'];
                } else {
                    if (isset($this->_baseinstalldirs[dirname($path)])) {
                        $this->_baseinstalldirs[$origpath] = $this->_baseinstalldirs[dirname($path)];
                    }
                }
            } elseif ($name === 'file') {
                while ($this->_lastFileDepth > $depth) {
                    $this->_path = dirname($this->_path);
                    if ($this->_path == '.') {
                        $this->_path = '';
                    } else {
                        $this->_path .= '/';
                    }
                    $this->_lastFileDepth--;
                    $this->_lastDepth--;
                }
                $path = $this->_path . $attribs['name'];
                if (isset($arr['file'][0])) {
                    $newarr = $arr['file'][count($arr['file']) - 1];
                } else {
                    $newarr = $arr['file'];
                }
                $newarr['attribs']['name'] =
                    $path;
                $this->_files[$path] = $newarr;
                $this->_curFile = $path;
                $this->_inFile = $depth;
            } elseif ($this->_inFile) {
                // add tasks
                $this->_files[$this->_curFile][$name] = $arr[$name];
            }
        } elseif ($name === 'contents') {
            $this->_inContents = true;
        }
        return $arr;
    }
}
?>
