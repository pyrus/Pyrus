<?php
class PEAR2_Pyrus_PackageFile_v2_Files implements ArrayAccess
{
    protected $array;
    function __construct(&$array)
    {
        $this->array = &$array;
    }

    function offsetUnset($var)
    {
        unset($this->array[$var]);
    }

    function offsetIsset($var)
    {
        return isset($this->array[$var]);
    }

    function offsetGet($var)
    {
        if (isset($this->array[$var])) {
            return $this->array[$var];
        }
        return null;
    }

    function offsetSet($var, $value)
    {
        if (!is_array($value)) {
            throw new PEAR2_PackageFile_v2_Files_Exception('File must be an array of
                attributes and tasks');
        }
        if (!isset($value['attribs'])) {
            // no tasks is assumed
            $value = array('attribs' => $value);
        }
        $value['attribs']['name'] = $var;
        if (!isset($value['attribs']['role'])) {
            throw new PEAR2_Pyrus_PackageFile_v2_Files_Exception('File role must be set for' .
                ' file ' . $var);
        }
        $this->array[$var] = $value;
    }

    function offsetExists($var)
    {
        return isset($this->array[$var]);
    }
}