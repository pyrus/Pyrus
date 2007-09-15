<?php
/**
 * Process an XML file, convert it to an array
 * @package PEAR2
 * @subpackage XML
 */
class PEAR2_Pyrus_XMLParser
{
    function parseString($string, $schema = false)
    {
        $a = new XMLReader;
        $a->XML($string);
        return $this->_parse($a, $string, $schema, false);
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
        $a = new XMLReader;
        $a->open($file);
        return $this->_parse($a, $file, $schema, true);
    }
    static function mergeTag(&$arr, $tag, $attr, $name)
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
                return;
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
            $arr[$name] = $tag;
        }
    }

    static function mergeValue(&$arr, $value, $name)
    {
        if (is_array($arr) && isset($arr[0])) {
            // multiple siblings
            $me = &$arr[count($arr[$name]) - 1];
        } elseif (is_array($arr)) {
            $me = &$arr;
        } else {
            $arr = $value;
            return;
        }
        if (isset($me['attribs'])) {
            $me['_content'] = $value;
        } else {
            $me = $value;
        }
    }

    private function _parse($a, $file, $schema, $isfile)
    {
        $arr = $tagStack = $level = array();
        $cur = &$arr;
        $prevStack = array(&$arr);
        while ($a->read()) {
            if ($a->nodeType == XMLReader::ELEMENT) {
                $tag = $a->name;
                if (isset($level[count($tagStack)]) && $level[count($tagStack)] == $tag) {
                    // next sibling tag
                    if (!is_array($cur[$tag]) || !isset($cur[$tag][0])) {
                        $cur[$tag] = array($cur[$tag]);
                    }
                }
                $attrs = array();
                if ($a->isEmptyElement) {
                    if ($a->hasAttributes) {
                        $attr = $a->moveToFirstAttribute();
                        while ($attr) {
                            $attrs[$a->name] = $a->value;
                            $attr = $a->moveToNextAttribute();
                        }
                        self::mergeTag($cur, '', $attrs, $tag);
                        continue;
                    }
                    self::mergeTag($cur, '', array(), $tag);
                    continue;
                }
                $prevStack[] = &$cur;
                $level[count($tagStack)] = $tag;
                $tagStack[] = $tag;
                if ($a->hasAttributes) {
                    $attr = $a->moveToFirstAttribute();
                    while ($attr) {
                        $attrs[$a->name] = $a->value;
                        $attr = $a->moveToNextAttribute();
                    }
                }
                self::mergeTag($cur, '', $attrs, $tag);
                $cur = &$cur[$tag];
                if (is_array($cur) && isset($cur[0])) {
                    // seek to last sibling
                    $cur = &$cur[count($cur) - 1];
                }
                continue;
            }
            if ($a->nodeType == XMLReader::END_ELEMENT) {
                $cur = &$prevStack[count($prevStack) - 1];
                array_pop($prevStack);
                unset($level[count($tagStack)]);
                $tag = array_pop($tagStack);
                continue;
            }
            if ($a->nodeType == XMLReader::TEXT || $a->nodeType == XMLReader::CDATA) {
                self::mergeValue($cur, $a->value, $tag);
            }
        }
        if ($schema) {
            $a = new DOMDocument();
            if ($isfile) {
                $a->load($file);
            } else {
                $a->loadXML($file);
            }
            libxml_use_internal_errors(true);
            $a->schemaValidate($schema);
            $causes = array();
            foreach (libxml_get_errors() as $error) {
                $causes[] = new PEAR2_Pyrus_XMLParser_Exception("Line " .
                     $error->line . ': ' . $error->message);
            }
            if (count($causes)) {
                throw new PEAR2_Pyrus_XMLParser_Exception('Invalid XML document', $causes);
            }
        }
        return $arr;
    }
}