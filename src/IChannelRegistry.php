<?php
interface PEAR2_Pyrus_IChannelRegistry
{
    public function add(PEAR2_Pyrus_ChannelFile $channel);
    public function update(PEAR2_Pyrus_ChannelFile $channel);
    public function delete($channel);
    public function exists($channel, $strict = true);
    public function parseName($name);
    public function parsedNameToString($name);
}