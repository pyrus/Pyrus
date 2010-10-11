<?php
include __DIR__ . '/../test_framework.php.inc';
$parser = new \PEAR2\Pyrus\PackageFile\Parser\v2;
class Mockv2 extends \PEAR2\Pyrus\PackageFile\v2
{
    public $arr;
    public $filelist;
    public $baseinstalls;
    public $packagefile;

    function setFilelist($list)
    {
        $this->filelist = $list;
    }

    function setBaseInstallDirs($dirs)
    {
        $this->baseinstalls = $dirs;
    }

    function setPackageFile($file)
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