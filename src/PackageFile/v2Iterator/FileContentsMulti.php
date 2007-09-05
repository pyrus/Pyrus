<?php
/**
 * iterator for tags with multiple sub-tags
 */
class PEAR2_Pyrus_PackageFile_v2Iterator_FileContentsMulti extends PEAR2_Pyrus_PackageFile_v2Iterator_FileContents
{
    function key()
    {
        return $this->tag;
    }
}
