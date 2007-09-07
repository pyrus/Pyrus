<?php
/**
 * Multi-Error Error aggregator
 *
 * This class is designed to be extended for specific use.  It codifies easy
 * ways of aggregating error conditions that don't necessarily require an exception
 * to be thrown, but do need an easy way to retrieve them.
 * 
 * Usage:
 * 
 * <code>
 * $multi = new PEAR2_Pyrus_MultiErrors;
 * $multi[E_WARNING] = new SomeException('Not serious');
 * $multi[E_WARNING] = new SomeException('Another not too serious');
 * $multi[E_ERROR] = new BadException('Really serious');
 * foreach ($multi as $e) {
 *     echo $e;
 * }
 * foreach ($multi[E_WARNING] as $e) {
 *     echo $e;
 * }
 * if (count($multi[E_ERROR])) {
 *     throw new PEAR2_Exception('Failure to do something', $multi);
 * }
 * </code>
 * @copyright 2007 Gregory Beaver
 * @package PEAR2_Pyrus_MultiErrors
 * @license http://www.php.net/license/3_0.txt PHP License
 */
class PEAR2_Pyrus_MultiErrors implements Iterator, Countable, ArrayAccess {

    /**
     * Errors are stored in the order that they are declared
     * @var array
     */
    private $_errors = array();

    /**
     * Storage of errors by level.
     *
     * Allows easy retrieval and deletion of only errors from a particular level
     * @var array
     */
    private $_errorsByLevel = array();

    /**
     * For usage by 
     *
     * @var unknown_type
     */
    private $_requestedLevel = false;

    public function current()
    {
        if ($this->_requestedLevel) {
            return current($this->_errorsByLevel[$this->_requestedLevel]);
        }
        return current($this->_errors);
    }

    public function key()
 	{
        if ($this->_requestedLevel) {
            return key($this->_errorsByLevel[$this->_requestedLevel]);
        }
        return key($this->_errors);
 	}

 	public function next()
 	{
        if ($this->_requestedLevel) {
            return next($this->_errorsByLevel[$this->_requestedLevel]);
        }
        return next($this->_errors);
 	}

 	public function rewind()
 	{
        if ($this->_requestedLevel) {
            return rewind($this->_errorsByLevel[$this->_requestedLevel]);
        }
        return rewind($this->_errors);
 	}

 	public function valid()
 	{
        return $this->current() !== false;
 	}

 	public function count()
 	{
 	    if ($this->_requestedLevel) {
 	        if (isset($this->_errorsByLevel[$this->_requestedLevel])) {
 	            return count($this->_errorsByLevel[$this->_requestedLevel]);
 	        }
 	        return 0;
 	    }
 	    return count($this->_errors);
 	}

 	public function offsetExists($offset)
 	{
 	    throw new PEAR2_Pyrus_MultiErrors_Exception('isset() is not implemented, use count()');
 	}

 	public function offsetGet ($offset)
 	{
 	    if (isset($this->_errorsByLevel[$offset])) {
 	        $save = $this->_requestedLevel;
 	        $this->_requestedLevel = $offset;
 	        $a = clone $this;
 	        $this->_requestedLevel = $save;
 	        return $a;
 	    }
 	    return null;
 	}

 	public function offsetSet ($offset, $value)
 	{
 	    if (!($value instanceof Exception)) {
 	        throw new PEAR2_Pyrus_MultiErrors_Exception('offsetSet: $value is not an Exception object');
 	    }
 	    if (!is_int($offset)) {
 	        throw new PEAR2_Pyrus_MultiErrors_Exception('offsetSet: $offset is not an integer');
 	    }
 	    if (in_array($offset, array(E_NOTICE, E_WARNING, E_ERROR))) {
            $count = count($this->_errors);
            $this->_errors[$count] = $err;
            $this->_errorsByLevel[$level][] = &$this->_errors[$count];
 	    }
 	}

 	public function offsetUnset ($offset)
 	{
 	    throw new PEAR2_Pyrus_MultiErrors_Exception('unset() is not implemented');
 	}

 	public function toArray()
 	{
 	    return $this->_errors;
 	}
}
?>