<?php
include __DIR__ . '/../test_framework.php.inc';
$parser = new \Pyrus\PackageFile\Parser\v2;
class Mockv2 extends \Pyrus\PackageFile\v2
{
    public $arr;
    public $filelist;
    public $baseinstalls;
    public $packagefile;

    function setFilelist(array $list)
    {
        $this->filelist = $list;
    }

    function setBaseInstallDirs(array $list)
    {
        $this->baseinstalls = $list;
    }

    function setPackageFile($file, $archive = false)
    {
        $this->packagefile = $file;
    }

    function getThingy()
    {
        return array(
            'filelist' => $this->filelist,
            'baseinstalls' => $this->baseinstalls,
            'packagefile' => $this->packagefile,
            'packageinfo' => $this->packageInfo);
    }
}
