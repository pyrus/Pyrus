<?php
interface PEAR2_Pyrus_IRegistry
{
    public function install(PEAR2_Pyrus_PackageFile_v2 $info);
    public function uninstall($name, $channel);
    public function exists($package, $channel);
    public function info($package, $channel, $field);
    public function listPackages($channel);
    public function __get($var);
    /**
     * @return PEAR2_Pyrus_PackageFile_v2
     */
    public function toPackageFile($package, $channel);
}