<?php
/**
 * Process an array, and serialize it into XML
 * @package PEAR_SimpleChannelServer
 * @subpackage XML
 */
class PEAR2_Pyrus_XMLWriter
{
    private $_array;
    private $_state;
    /**
     * @var XMLWriter
     */
    private $_writer;
    private $_iter;
    private $_tagStack;
    private $_namespacesStack;
    private $_namespaces;
    private $_tag;
    private $_expectedDepth;
    private $_type;
    function __construct(array $array)
    {
        if (count($array) != 1) {
            throw new PEAR2_Pyrus_XMLWriter_Exception('Cannot serialize array to' .
                'XML, array must have exactly 1 element');
        }
        $this->_array = $array;
        $this->_writer = new XMLWriter;
    }

    function __toString()
    {
        $this->_writer->openMemory();
        return $this->_serialize();
    }

    function toFile($file)
    {
        $this->_writer->openUri($file);
        return $this->_serialize();
    }

    private function _pushState()
    {
        $this->_state[] = array($this->_type, $this->_tag, $this->_expectedDepth);
    }

    private function _popState()
    {
        list($this->_type, $this->_tag, $this->_expectedDepth) =
            array_pop($this->_state);
    }

    private function _finish($key, $values)
    {
        switch ($this->_type) {
            case 'Attribs' :
                $this->_popState();
                return false;
            case 'Tag' :
                $this->_popState();
                $this->_writer->endElement();
                return false;
            case 'Sibling' :
                $this->_popState();
                return false;
        }
    }

    private function _startElement($key, $values)
    {
        // new element
        if (strpos($key, ':')) {
            // namespaced element
            list($ns, $element) = explode(':', $key);
            if (is_string($values)) {
                $this->_writer->writeElementNs($ns, $element, $this->_namespaces[$ns], $values);
            } else {
                $this->_writer->startElementNs($ns, $element, $this->_namespaces[$ns]);
            }
        } else {
            if (is_string($values)) {
                $this->_writer->writeElement($key, $values);
            } else {
                $this->_writer->startElement($key);
            }
        }
    }

    private function _handleTag($key, $values)
    {
        if (is_int($key)) {
            $this->_type = 'Sibling';
            $this->_expectedDepth = $this->_iter->getDepth();
            $this->_pushState();
            // handle sibling tags
            return '_handleSibling';
        }
        $this->_startElement($key, $values);
        if (!is_string($values)) {
            $this->_expectedDepth = $this->_iter->getDepth();
            $this->_pushState();
            $this->_tag = $key;
        }
        // cycle to next key
        return false;
    }

    private function _handleSibling($key, $values)
    {
        if (is_int($key) && $this->_iter->getDepth() == $this->_expectedDepth) {
            if ($key && !is_string($values)) {
                $this->_startElement($this->_tag, $values);
                $this->_pushState();
            }
        }
        if (!is_string($values)) {
            $this->_type = 'Tag';
            $this->_expectedDepth = $this->_iter->getDepth() + 1;
            // handle internal tags
        }
        // cycle to next key
        return false;
    }

    private function _handleAttribs($key, $values)
    {
        // xmlwriter converts these to &#10; and &#13;.  Bad.
        $values = str_replace(array("\n","\r"), array('', ''), $values);
        if (strpos($key, ':')) {
            // namespaced
            list($ns, $attr) = explode(':', $key);
            if ($ns == 'xmlns') {
                // new namespace declaration
                if (isset($this->_namespaces[$attr]) && !isset($this->_namespacesStack[$depth])) {
                    // save the current namespace, will restore
                    // at element end
                    $this->_namespacesStack[$depth] = $this->_namespaces;
                }
                $this->_namespaces[$attr] = $values;
                $this->_writer->writeAttribute($key, $values);
            } else {
                $this->_writer->writeAttributeNS($ns, $attr, $this->_namespaces[$ns], $values);
            }
        } else { // default namespace
            $this->_writer->writeAttribute($key, $values);
        }
        // cycle to next key
        return false;
    }

    private function _serialize()
    {
        $this->_namespaces = array();
        $this->_namespacesStack = array();
        $this->_tagStack = array();
        $this->_writer->setIndent(true);
        $this->_writer->setIndentString(' ');
        $this->_writer->startDocument('1.0', 'UTF-8');
        $this->_state = array();
        $this->_type = 'Tag';
        $this->_expectedDepth = 0;
        foreach ($this->_iter = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->_array),
                     RecursiveIteratorIterator::SELF_FIRST) as $key => $values) {
            $depth = $this->_iter->getDepth();
            while ($this->_iter->getDepth() < $this->_expectedDepth) {
                // finished with this tag
                $this->_finish($key, $values);
            }
            if ($this->_type !== 'Attribs') {
                if ($key === '_content') {
                    $this->_writer->text($values);
                    continue;
                }
                if ($key === 'attribs') {
                    // attributes are 1 depth higher
                    $this->_pushState();
                    $this->_expectedDepth = $this->_iter->getDepth() + 1;
                    $this->_type = 'Attribs';
                    // cycle to first attribute
                    continue;
                }
            }
            $next = '_handle' . $this->_type;
            while ($next = $this->{$next}($key, $values));
        }
        $this->_writer->endDocument();
        return $this->_writer->flush();
    }
}