<?php
class PEAR2_Pyrus_PackageFile
{
    public $info;
    public $path;
    function __construct($package, $class = 'PEAR2_Pyrus_PackageFile_Parser_v2')
    {
        $this->path = $package;
        $parser = new $class;
        $data = file_get_contents($package);
        $this->info = $parser->parse($data, $package);
    }

    function __toString()
    {
        return $this->info->__toString();
    }
}
