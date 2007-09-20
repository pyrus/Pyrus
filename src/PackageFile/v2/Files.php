<?php
class PEAR2_Pyrus_PackageFile_v2_Files extends ArrayObject
{
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
        return parent::offsetSet($var, $value);
    }
}