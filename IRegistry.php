<?php
interface PEAR2_Pyrus_IRegistry
{
    public function installPackage(PEAR2_Pyrus_PackageFile_v2 $info);
    public function upgradePackage(PEAR2_Pyrus_PackageFile_v2 $info);
    public function uninstallPackage($name, $channel);
    public function addChannel(PEAR2_Pyrus_ChannelFile $channel);
    public function updateChannel(PEAR2_Pyrus_ChannelFile $channel);
    public function deleteChannel($channel);
    public function packageExists($package, $channel);
    public function channelAlias($channel);
    public function channelExists($channel, $strict = true);
    public function hasMirror($channel, $mirror);
    public function setAlias($channel, $alias);
    public function getMirrors($channel);
    public function packageInfo($package, $channel, $field);
    public function __get($var);
}