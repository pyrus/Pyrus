<?php
/**
 * Traverse the complete <contents> tag, one <dir> at a time
 */
class PEAR2_Pyrus_PackageFile_v2Iterator_File extends RecursiveIteratorIterator
{
    function next()
    {
        parent::next();
        $x = $this->current();
        if (isset($x[0])) {
            parent::next();
            $x = $this->current();
        }
    }
}