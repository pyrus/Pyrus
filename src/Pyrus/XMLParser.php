<?php
/**
 * PEAR2_Pyrus_XMLParser
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
 * Process an XML file, convert it to an array
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @subpackage XML
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_XMLParser
{
    protected $reader;
    function __construct()
    {
        $this->reader = new XMLReader;
    }

    function parseString($string, $schema = false)
    {
        $this->reader->XML($string);
        return $this->_parse($string, $schema, false);
    }

    /**
     * Using XMLReader, unserialize XML into an array
     *
     * This unserializer has limitations on the XML it can parse, for simplicity:
     *
     *  - Only a single text node (the last one) will be processed, so this code:
     *    <pre>
     *     <?xml version="1.0" ?><test>hi<tag/>there</test>
     *    </pre>
     *    results in <code>array('test' => array('tag' => '', '_content' => 'there'))</code>
     *  - tag ordering is not preserved in all cases:
     *    <pre>
     *     <?xml version="1.0" ?><test><tag /><another /> <tag /></test>
     *    </pre>
     *    results in
     *    <code>array('test' => array('tag' => array('', ''), 'another' => ''))</code>
     * @param string $file file URI to process
     * @return array
     */
    function parse($file, $schema = false)
    {
        if (!$this->reader->open($file)) {
            throw new PEAR2_Pyrus_XMLParser_Exception('Cannot open ' . $file .
                ' for parsing');
        }
        return $this->_parse($file, $schema, true);
    }

    protected function mergeTag($arr, $tag, $attr, $name, $depth)
    {
        if ($attr) {
            // tag has attributes
            if (is_string($tag) && $tag !== '') {
                $tag = array('attribs' => $attr, '_content' => $tag);
            } else {
                if (!is_array($tag)) {
                    $tag = array();
                }
                $tag['attribs'] = $attr;
            }
        }
        if (is_array($arr) && isset($arr[$name]) && is_array($arr[$name]) &&
              isset($arr[$name][0])) {
            // tag exists as a sibling
            $where = count($arr[$name]);
            if (!isset($arr[$name][$where])) {
                $arr[$name][$where] = $tag;
                return $arr;
            }
            if (!is_array($arr[$name][$where])) {
                if (strlen($arr[$name][$where])) {
                    $arr[$name][$where] = array('_content' => $arr[$name][$where]);
                } else {
                    $arr[$name][$where] = array();
                }
            }
            $arr[$name][$where] = $tag;
        } else {
            if (!is_array($arr)) {
                $arr = array();
            }
            if (isset($arr[$name])) {
                // new sibling
                $arr[$name] = array($arr[$name], $tag);
                return $arr;
            }
            $arr[$name] = $tag;
        }
        return $arr;
    }

    protected function mergeValue($arr, $value)
    {
        if (is_array($arr) && isset($arr[0])) {
            // multiple siblings
            $arr[count($arr) - 1] = $this->mergeActualValue(
                $arr[count($arr) - 1], $value);
        } elseif (is_array($arr)) {
            $arr = $this->mergeActualValue($arr, $value);
        } else {
            $arr = $value;
        }
        return $arr;
    }

    protected function mergeActualValue($me, $value)
    {
        if (count($me)) {
            $me['_content'] = $value;
        } else {
            $me = $value;
        }
        return $me;
    }

    private function _parse($file, $schema, $isfile)
    {
        $arr = $this->_recursiveParse();
        $this->reader->close();
        if ($schema) {
            if (!file_exists($schema)) {
                throw new PEAR2_Pyrus_XMLParser_Exception('Schema "' . $schema . '" ' .
                                                          'does not exist');
            }
            $a = new DOMDocument();
            if ($isfile) {
                $a->load($file);
            } else {
                $a->loadXML($file);
            }
            libxml_use_internal_errors(true);
            libxml_clear_errors();
            $a->schemaValidate($schema);
            $causes = array();
            foreach (libxml_get_errors() as $error) {
                $causes[] = new PEAR2_Pyrus_XMLParser_Exception("Line " .
                     $error->line . ': ' . $error->message);
            }
            libxml_clear_errors();
            if (count($causes)) {
                throw new PEAR2_Pyrus_XMLParser_Exception('Invalid XML document', $causes);
            }
        }

        return $arr;
    }

    private function _recursiveParse($arr = array())
    {
        while ($this->reader->read()) {
            $depth = $this->reader->depth;
            if ($this->reader->nodeType == XMLReader::ELEMENT) {
                $tag = $this->reader->name;

                $attrs = array();
                if ($this->reader->isEmptyElement) {
                    if ($this->reader->hasAttributes) {
                        $attr = $this->reader->moveToFirstAttribute();
                        while ($attr) {
                            $attrs[$this->reader->name] = $this->reader->value;
                            $attr = $this->reader->moveToNextAttribute();
                        }
                        $depth = $this->reader->depth;
                        $arr = $this->mergeTag($arr, '', $attrs, $tag, $depth);
                        continue;
                    }
                    $depth = $this->reader->depth;
                    $arr = $this->mergeTag($arr, '', array(), $tag, $depth);
                    continue;
                }
                if ($this->reader->hasAttributes) {
                    $attr = $this->reader->moveToFirstAttribute();
                    while ($attr) {
                        $attrs[$this->reader->name] = $this->reader->value;
                        $attr = $this->reader->moveToNextAttribute();
                    }
                }
                $depth = $this->reader->depth;
                $arr = $this->mergeTag($arr, '', $attrs, $tag, $depth);
                if (is_array($arr[$tag]) && isset($arr[$tag][0])) {
                    // seek to last sibling
                    $arr[$tag][count($arr[$tag]) - 1] =
                        $this->_recursiveParse($arr[$tag][count($arr[$tag]) - 1]);
                } else {
                    $arr[$tag] = $this->_recursiveParse($arr[$tag]);
                }
                continue;
            }
            if ($this->reader->nodeType == XMLReader::END_ELEMENT) {
                return $arr;
            }
            if ($this->reader->nodeType == XMLReader::TEXT ||
                  $this->reader->nodeType == XMLReader::CDATA) {
                $arr = $this->mergeValue($arr, $this->reader->value);
            }
        }
        return $arr;
    }
}
