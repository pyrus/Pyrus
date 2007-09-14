<?php
/**
 * Process an XML file, convert it to an array
 * @package PEAR2
 * @subpackage XML
 */
class PEAR2_Pyrus_XMLParser
{

    static function prepareNewTag(&$arr, $depth, $name)
    {
        
    }

    /**
     * Recursively merge in new XML values
     *
     * @param array $arr
     * @param array $depth array of tag names in depth-last order
     * @param mixed $value the content to merge in
     */
    static function mergeValue(&$arr, $depth, $value, $visited)
    {
        if (!count($depth)) {
            if (is_string($arr) && strlen($arr)) {
                if (is_array($value)) {
                    $arr = array('_content' => $arr);
                    $arr[] = $value;
                } else {
                    $arr = array($arr);
                    $arr[] = $value;
                }
                return;
            }
            if (is_array($arr)) {
                if (is_array($value) && !isset($arr[0])) {
                    $arr = array($arr);
                }
                if (is_string($value)) {
                    $arr['_content'] = $value;
                    return;
                }
                $arr[] = $value;
                return;
            }
            $arr = $value;
            return;
        }
        $key = array_shift($depth);
        if (!isset($arr[$key])) {
            $arr[$key] = count($depth) ? array() : null;
        } else {
            if (is_string($arr[$key]) && count($depth) && strlen($arr[$key])) {
                $arr[$key] = array('_content' => $arr[$key]);
            }
        }
        self::mergeValue($arr[$key], $depth, $value);
    }

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

    private function _wasVisited($visited, $count, $name)
    {
        if (!isset($visited[$count])) {
            return false;
        }
        if (!isset($visited[$count][$name])) {
            return false;
        }
        return true;
    }

    private function _setVisited(&$visited, $count, $name)
    {
        if (!isset($visited[$count])) {
            $visited[$count] = array();
        }
        if (!isset($visited[$count][$name])) {
            $visited[$count][$name] = 0;
        }
        ++$visited[$count][$name];
    }

    private function _parse($a, $file, $schema, $isfile)
    {
        $visited = array();
        $tagStack = array();
        $arr = array();
        while ($a->read()) {
            if ($a->nodeType == XMLReader::ELEMENT) {
                if ($a->isEmptyElement) {
                    if ($a->hasAttributes) {
                        $attrs = array();
                        $attr = $a->moveToFirstAttribute();
                        while ($attr) {
                            $attrs[$a->name] = $a->value;
                            $attr = $a->moveToNextAttribute();
                        }
                        self::mergeValue($arr, 
                            array_merge($tagStack, array($a->name, 'attribs')),
                            $attrs);
                        continue;
                    }
                    if ($this->_wasVisited($visited, count($tagStack), $a->name)) {
                        self::prepareNewTag($arr, $tagStack, $a->name);
                    }
                    $this->_setVisited($visited, count($tagStack), $a->name, $visited);
                    $visited[count($tagStack)][$a->name] = 
                    self::mergeValue($arr,
                        array_merge($tagStack, array($a->name)), '');
                    continue;
                }
                $tagStack[] = $a->name;
                if ($this->_wasVisited($visited, count($tagStack), $a->name)) {
                    self::prepareNewTag($arr, $tagStack, $a->name);
                }
                $this->_setVisited($visited, count($tagStack), $a->name);
                if ($a->hasAttributes) {
                    $attrs = array();
                    $attr = $a->moveToFirstAttribute();
                    while ($attr) {
                        $attrs[$a->name] = $a->value;
                        $attr = $a->moveToNextAttribute();
                    }
                    self::mergeValue($arr,
                        array_merge($tagStack, array('attribs')), $attrs, $visited);
                }
                continue;
            }
            if ($a->nodeType == XMLReader::END_ELEMENT) {
                array_pop($tagStack);
                continue;
            }
            if ($a->nodeType == XMLReader::TEXT || $a->nodeType == XMLReader::CDATA) {
                self::mergeValue($arr,
                    $tagStack, $a->value);
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