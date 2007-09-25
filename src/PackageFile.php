<?php
class PEAR2_Pyrus_PackageFile
{
    public $info;
    public $path;
    function __construct($package)
    {
        $this->path = $package;
        $parser = new PEAR2_Pyrus_PackageFile_Parser_v2;
        $data = file_get_contents($package);
        $this->info = $parser->parse($data, $package);
    }

    function __toString()
    {
        return $this->info->__toString();
    }
}
