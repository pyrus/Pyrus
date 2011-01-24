<?php
/**
 * \PEAR2\Pyrus\DirectedGraph\Vertex
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * Class to represent vertices within the dependency directed graph.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\DirectedGraph;
class Vertex implements \ArrayAccess, \Countable, \Iterator
{
    const WHITE = \PEAR2\Pyrus\DirectedGraph::WHITE;
    const GRAY = \PEAR2\Pyrus\DirectedGraph::GRAY;
    const BLACK = \PEAR2\Pyrus\DirectedGraph::BLACK;
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
            throw new Exception('data must be an object, was ' . gettype($data));
        }

        $this->data = $data;
    }

    /**
     * Connect to another vertex
     *
     * @param \PEAR2\Pyrus\DirectedGraph\Vertex $to
     */
    function connect(Vertex $to)
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
        if ($value instanceof Vertex) {
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