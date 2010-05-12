<?php
/**
 * \PEAR2\Pyrus\XMLWriter
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @author    Helgi Þormar Þorbjörnsson <helgi@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Process an array, and serialize it into XML
 *
 * @category   PEAR2
 * @package    PEAR2_Pyrus
 * @subpackage XML
 * @author     Greg Beaver <cellog@php.net>
 * @author     Helgi Þormar Þorbjörnsson <helgi@php.net>
 * @copyright  2010 The PEAR Group
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus;
class XMLWriter extends \XMLWriter
{
    private $_array;
    private $_state;

    /**
     * @var XMLWriter
     */
    private $_iter;
    private $_tagStack;
    private $_namespaces;
    private $_tag;
    private $_expectedDepth;
    private $_type;
    private $_lastkey;

    /**
     * Construct a new xml writer object.
     * <code>
     * $xmlarray = array('channel'=>array('name'=>'pear2.php.net'));
     * $channel = new \PEAR2\Pyrus\XMLWriter($xmlarray);
     * </code>
     *
     * @param array $array Array representing the XML data.
     */
    function __construct(array $array)
    {
        if (count($array) != 1) {
            throw new XMLWriter\Exception('Cannot serialize array to XML, ' .
                'array must have exactly 1 element');
        }

        $this->_array = $array;
    }

    /**
     * Return the raw xml string representation.
     *
     * @return string
     */
    function __toString()
    {
        $this->openMemory();
        return $this->_serialize();
    }

    function toFile($file)
    {
        $this->openUri($file);
        return $this->_serialize();
    }

    private function _pushState()
    {
        $this->_state[] = array($this->_type, $this->_tag, $this->_expectedDepth,
            $this->_namespaces);
    }

    private function _popState()
    {
        $save = $this->_namespaces;
        list($this->_type, $this->_tag, $this->_expectedDepth, $this->_namespaces) =
            array_pop($this->_state);

        foreach ($save as $ns) {
            if (!isset($this->_namespaces[$ns])) {
                // all namespaces must exist - only overriding is allowed
                $this->_namespaces = $save;
                return;
            }
        }
    }

    private function _finish($key, $values)
    {
        // Types: Attribs, Tag, Sibling
        if ($this->_type == 'Attribs' || $this->_type == 'Sibling') {
            $this->_popState();
        } elseif ($this->_type == 'Tag') {
            $this->_popState();
            $this->endElement();
        }

        return false;
    }

    private function _startElement($key, $values)
    {
        // new element
        if (strpos($key, ':')) {
            // namespaced element
            list($ns, $element) = explode(':', $key);
        }

        if (isset($element) && !isset($this->_namespaces[$ns])) {
            if (is_string($values)) {
                if (strlen($values)) {
                    $this->writeElementNs($ns, $element, $this->_namespaces[$ns], $values);
                } else {
                    $this->writeElementNs($ns, $element, $this->_namespaces[$ns]);
                }
            } else {
                $this->startElementNs($ns, $element, $this->_namespaces[$ns]);
            }
        } else {
            if (is_string($values) || is_int($values) || is_bool($values)) {
                if (strlen($values)) {
                    $this->writeElement($key, $values);
                } else {
                    $this->writeElement($key);
                }
            } else {
                $this->startElement($key);
            }
        }
    }

    /**
     * Handle an individual tag/element in the XML
     *
     * @param mixed $key    The key for this element.
     * @param mixed $values The contents of this tag/element.
     */
    private function _handleTag($key, $values)
    {
        if (is_int($key)) {
            $this->_type          = 'Sibling';
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
            if ($key) {
                $this->_startElement($this->_tag, $values);
                if (!is_string($values)) {
                    $this->_pushState();
                }
            } else {
                if (is_string($values)) {
                    $this->text($values);
                    $this->endElement();
                }
            }
        }

        if (!is_string($values)) {
            $this->_type          = 'Tag';
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
            if ($ns == 'xmlns' || isset($this->_namespaces[$ns])) {
                if ($ns == 'xmlns') {
                    // new namespace declaration
                    $this->_namespaces[$attr] = $values;
                }

                $this->writeAttribute($key, $values);
            } else {
                $this->writeAttributeNS($ns, $attr, $values, $values);
            }
        } else { // default namespace
            $this->writeAttribute($key, $values);
        }

        // cycle to next key
        return false;
    }

    /**
     * @access private
     *
     * @return bool
     */
    public static function _filter($a)
    {
        if ($a === false) {
            return false;
        }

        return true;
    }

    /**
     * Utilize custom serialization for XMLWriter object, to convert object
     * to SQL.
     *
     * @return string
     */
    private function _serialize()
    {
        $this->setIndent(true);
        $this->setIndentString(' ');
        $this->startDocument('1.0', 'UTF-8');
        $this->_namespaces    = array();
        $this->_tagStack      = array();
        $this->_state         = array();
        $this->_type          = 'Tag';
        $this->_expectedDepth = 0;
        $this->_lastkey       = array();
        $lastdepth            = 0;
        foreach ($this->_iter = new \RecursiveIteratorIterator(
                        new \RecursiveArrayIterator($this->_array),
                        \RecursiveIteratorIterator::SELF_FIRST) as $key => $values) {
            $depth = $this->_iter->getDepth();
            while ($depth < $this->_expectedDepth) {
                // finished with this tag
                $this->_finish($key, $values);
                $lastdepth--;
            }

            if (isset($this->_lastkey[$depth]) && $key != $this->_lastkey[$depth]) {
                while ($lastdepth > $depth) {
                    $this->_finish($key, $values);
                    $lastdepth--;
                }
            }

            $this->_lastkey[$depth] = $key;
            foreach ($this->_lastkey as $d => &$k) {
                if ($d > $depth) {
                    $k = false;
                }
            }

            $this->_lastkey = array_filter($this->_lastkey, array('PEAR2\Pyrus\XMLWriter', '_filter'));
            $lastdepth = $depth;
            if ($this->_type !== 'Attribs') {
                if ($key === '_content') {
                    $this->text($values);
                    continue;
                }

                if ($key === 'attribs') {
                    // attributes are 1 depth higher
                    $this->_pushState();
                    $this->_expectedDepth = $this->_iter->getDepth() + 1;
                    $this->_type          = 'Attribs';
                    // cycle to first attribute
                    continue;
                }
            }

            $next = '_handle' . $this->_type;
            while ($next = $this->{$next}($key, $values));
        }

        while ($lastdepth) {
            $this->_finish($key, $values);
            $lastdepth--;
        }

        $this->endDocument();
        return $this->flush();
    }
}