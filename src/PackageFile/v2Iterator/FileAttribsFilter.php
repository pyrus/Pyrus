<?php
/**
 * Filter out the attributes meta-information when traversing the file list
 */
class PEAR2_Pyrus_PackageFile_v2Iterator_FileAttribsFilter extends RecursiveFilterIterator
{
    function accept()
    {
        $it = $this->getInnerIterator(); 
        if (!$it->valid()) {
            return false;
        }
        $key = $it->key();
        if ($key === 'attribs') {
            return false;
        }
        return true;
    }
}
