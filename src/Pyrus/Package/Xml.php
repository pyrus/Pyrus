<?php
class PEAR2_Pyrus_Package_Xml extends PEAR2_Pyrus_Package_Base
{
    private $_file;
    function __construct($package, PEAR2_Pyrus_Package $parent)
    {
        $this->_file = $package;
        parent::__construct(new PEAR2_Pyrus_PackageFile($package), $parent);
    }

    function getLocation()
    {
        return dirname($this->packagefile->path);
    }

    function getFileContents($file, $asstream = false)
    {
        $file = dirname($this->_file) . DIRECTORY_SEPARATOR . $file;
        return ($asstream ? fopen($file, 'rb') : file_get_contents($file));
    }
}