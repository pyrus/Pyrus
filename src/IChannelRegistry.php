<?php
interface PEAR2_Pyrus_IChannelRegistry
{
    public function add(PEAR2_Pyrus_ChannelFile $channel);
    public function update(PEAR2_Pyrus_ChannelFile $channel);
    public function delete($channel);
    public function getAlias($channel);
    public function exists($channel, $strict = true);
    public function hasMirror($channel, $mirror);
    public function setAlias($channel, $alias);
    public function getMirrors($channel);
    public function getObject($channel);
    public function parseName($name);
    public function parsedNameToString($name);
}