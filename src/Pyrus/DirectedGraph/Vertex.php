<?php
/**
 * PEAR2_Pyrus_DirectedGraph_Vertex
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
 * Class to represent vertices within the dependency directed graph.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_DirectedGraph_Vertex implements ArrayAccess, Countable, Iterator
{
    const WHITE = PEAR2_Pyrus_DirectedGraph::WHITE;
    const GRAY = PEAR2_Pyrus_DirectedGraph::GRAY;
    const BLACK = PEAR2_Pyrus_DirectedGraph::BLACK;
    protected $color = self::WHITE;
    public $data;
    protected $connections = array();

    /**
     * Encapsulate data within a directed graph vertex
     *
     * @param object $data
     */
    function __construct($data)
    {
        if (!is_object($data)) {
            throw new PEAR2_Pyrus_DirectedGraph_Exception('data must be an object, was ' .
                gettype($data));
        }

        $this->data = $data;
    }

    /**
     * Connect to another vertex
     *
     * @param PEAR2_Pyrus_DirectedGraph_Vertex $to
     */
    function connect(PEAR2_Pyrus_DirectedGraph_Vertex $to)
    {
        $this->connections[spl_object_hash($to)] = $to;
    }

    /**
     * Set the color of a visited node
     *
     * WHITE = unvisited, GRAY = visited, BLACK = finished
     *
     * @param self::WHITE|self::GRAY|self::BLACK|null $color if null, return the current color
     * @return int
     */
    function color($color = null)
    {
        if ($color === null) {
            return $this->color;
        }

        $this->color = $color;
    }

    function count()
    {
        $count = count($this->connections);
        foreach ($this->connections as $node) {
            if ($node->color() != self::WHITE) {
                --$count;
            }
        }

        return $count;
    }

    function offsetGet($var)
    {
        return $this->connections[$var];
    }

    function offsetSet($var, $value)
    {
        if ($value instanceof PEAR2_Pyrus_DirectedGraph_Vertex) {
            $this->connect($value);
        }
    }

    function offsetExists($var)
    {
        return isset($this->connections[$var]);
    }

    function offsetUnset($var)
    {
        unset($this->connections[$var]);
    }

    function current()
    {
        return current($this->connections);
    }

    function next()
    {
        return next($this->connections);
    }

    function key()
    {
        return key($this->connections);
    }

    function valid()
    {
        return current($this->connections);
    }

    function rewind()
    {
        reset($this->connections);
    }
}