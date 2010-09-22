<?php

/**
 * A container for HTTP request headers provides case insensitive access
 *
 * Array style access and object style access is provided
 *
 * Lazy processing of case insensitive access is provided so we don't strtolower the keys
 * until the headers are actually used
 *
 * @todo decide if this should be pulled out of the package and provided as a generic class
 */
namespace PEAR2\HTTP\Request;
class Headers implements \Iterator, \ArrayAccess, \Countable
{

    const ORIGINAL_CASE = 'fields';
    const LOWER_CASE = 'lowerCase';
    const CAMEL_CASE = 'camelCase';

    public $iterationStyle = self::LOWER_CASE;

    protected $fields = array();
    protected $camelCase = null;
    protected $lowerCase = null;

    /**
     * Takes the headers to provide access too
     */
    public function __construct($fields)
    {
        $this->fields = $fields;
    }

    /**
     * Magic getter for object access
     */
    public function __get($key)
    {
        if (is_null($this->camelCase)) {
            $this->camelCaseFields();
        }

        if (isset($this->camelCase[$key])) {
            return $this->camelCase[$key];
        }
        return null;
    }

    /**
     * Magic setter for object access
     *
     * @todo set $this->fields and $this->lowerCase
     */
    public function __set($key,$value)
    {
        $this->camelCase[$key] = $value;
    }

    /**
     * Magic isset for object access
     */
    public function __isset($key)
    {
        if (is_null($this->camelCase)) {
            $this->camelCaseFields();
        }
        return isset($this->camelCase[$key]);
    }

    /**
     * Magic unset for object access
     */
    public function __unset($key)
    {
        if (is_null($this->camelCase)) {
            $this->camelCaseFields();
        }
        unset($this->camelCase[$key]);
    }

    /**
     * ArrayAccess Implementation
     */
    public function offsetExists($key)
    {
        if (is_null($this->lowerCase)) {
            $this->lowerCaseFields();
        }
        $key = strtolower($key);
        return isset($this->lowerCase[$key]);
    }

    /**
     * ArrayAccess Implementation
     */
    public function offsetGet($key)
    {
        if (is_null($this->lowerCase)) {
            $this->lowerCaseFields();
        }
        $key = strtolower($key);
        if (isset($this->lowerCase[$key])) {
            return $this->lowerCase[$key];
        }
        return null;
    }

    /**
     * ArrayAccess Implementation
     */
    public function offsetSet($key,$value)
    {
        $key = strtolower($key);

        $this->lowerCase[$key] = $value;
    }

    /**
     * ArrayAccess Implementation
     */
    public function offsetUnset($key)
    {
        if (is_null($this->lowerCase)) {
            $this->lowerCaseFields();
        }
        $key = strtolower($key);
        unset($this->lowerCase[$key]);
    }

    /**
     * Return the number of headers
     * Countable interface implmentation
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * Iterator Implmentation
     */
    public function current()
    {
        return current($this->{$this->iterationStyle});
    }

    /**
     * Iterator Implementation
     */
    public function key()
    {
        return key($this->{$this->iterationStyle});
    }

    /**
     * Iterator Implmentation
     */
    public function next()
    {
        return next($this->{$this->iterationStyle});
    }

    /**
     * Iterator Implmentation
     */
    public function rewind()
    {
        if (is_null($this->{$this->iterationStyle})) {
            $m = $this->iterationStyle."Fields";
            $this->$m();
        }
        reset($this->{$this->iterationStyle});
    }

    /**
     * Iterator Implmentation
     */
    public function valid()
    {
        return (boolean) current($this->{$this->iterationStyle});
    }

    /**
     * Make all keys lower case
     */
    protected function lowerCaseFields()
    {
        $fields = array();
        foreach($this->fields as $k => $v) {
            $fields[strtolower($k)] = $v;
        }
        $this->lowerCase = $fields;
    }

    /**
     * Make all keys camel case, removing dashes
     */
    protected function camelCaseFields()
    {
        $fields = array();
        foreach($this->fields as $k => $v) {
            $pieces = explode('-',$k);
            $pieces = array_map('ucfirst',$pieces);
            $fields[implode('',$pieces)] = $v;
        }
        $this->camelCase = $fields;
    }
}
