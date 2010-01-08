<?php
/**
 * \pear2\Pyrus\DirectedGraph
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
 * Implements a graph data type, used for topological sorting of packages.
 *
 * This structure allows us to sort dependencies into the correct order for installation.
 * Iteration uses a depth-first search to perform a topological sort.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace pear2\Pyrus;
class DirectedGraph implements \Iterator
{
    const WHITE = 0;
    const GRAY = 1;
    const BLACK = 2;
    protected $vertices = array();
    /**
     * Map data to abstract vertex
     *
     * @var array
     */
    protected $map = array();
    /**
     * Topologically sorted vertices
     * @var array
     */
    protected $blackVertices = array();

    /**
     * Add a data vertex
     *
     * @param object $data
     * @return \pear2\Pyrus\DirectedGraph\Vertex
     */
    function add($data)
    {
        $vertex = new DirectedGraph\Vertex($data);
        $this->vertices[spl_object_hash($vertex)] = $vertex;
        $this->map[spl_object_hash($data)] = spl_object_hash($vertex);
        return $vertex;
    }

    /**
     * Connect two vertices in a directed graph
     *
     * This can be used with a fluent interface
     * @param object|\pear2\Pyrus\DirectedGraph\Vertex $from
     * @param object|\pear2\Pyrus\DirectedGraph\Vertex $to
     * @return \pear2\Pyrus\DirectedGraph
     */
    function connect($from, $to)
    {
        if ($from instanceof DirectedGraph\Vertex) {
            $a = spl_object_hash($from);
        } else {
            if (!isset($this->map[spl_object_hash($from)])) {
                $a = $this->add($from);
            } else {
                $a = $this->vertices[$this->map[spl_object_hash($from)]];
            }
        }

        if ($to instanceof DirectedGraph\Vertex) {
            $b = spl_object_hash($to);
        } else {
            if (!isset($this->map[spl_object_hash($to)])) {
                $b = $this->add($to);
            } else {
                $b = $this->vertices[$this->map[spl_object_hash($to)]];
            }
        }

        $this->vertices[spl_object_hash($a)]->connect($b);
        return $this;
    }

    function current()
    {
        return current($this->blackVertices)->data;
    }

    function next()
    {
        return next($this->blackVertices);
    }

    function key()
    {
        return key($this->blackVertices);
    }

    function valid()
    {
        return current($this->blackVertices);
    }

    function rewind()
    {
        $this->topologicalSort();
    }

    /**
     * Sort the vertices by their connections
     */
    function topologicalSort()
    {
        $this->blackVertices = array();
        if (!count($this->vertices)) {
            return array();
        }

        foreach ($this->vertices as $vertex) {
            $vertex->color(self::WHITE);
        }

        while (count($this->blackVertices) <  count($this->vertices)) {
            // select a vertex to start
            foreach ($this->vertices as $vertex) {
                if ($vertex->color() == self::BLACK) {
                    // already sorted
                    continue;
                }

                break;
            }

            do {
                // this vertex has been discovered
                $vertex->color(self::GRAY);
                if (!count($vertex)) {
                    // no adjacent edges
                    $this->blackVertices[] = $vertex;
                    $vertex->color(self::BLACK);
                    continue 2;
                }

                $black = true;
                // iterate over adjacent vertices to find a white vertex
                foreach ($vertex as $edge) {
                    if ($edge->color() == self::BLACK) {
                        continue;
                    }

                    if (!count($edge)) {
                        // no adjacent undiscovered vertices, we found a black one
                        $edge->color(self::BLACK);
                        $this->blackVertices[] = $edge;
                        continue;
                    }

                    $black = false;
                    $edge->color(self::GRAY);
                }

                if ($black) {
                    // found a new vertex
                    $this->blackVertices[] = $vertex;
                    $vertex->color(self::BLACK);
                } else {
                    foreach ($vertex as $edge) {
                        if ($edge->color() == self::BLACK) {
                            continue;
                        }

                        $vertex = $edge;
                        break;
                    }
                }
            } while (!$black);
        }

        return array_map(function($a){return $a->data;}, $this->blackVertices);
    }
}