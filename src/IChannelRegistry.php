<?php
interface PEAR2_Pyrus_IChannelRegistry
{
    public function add(PEAR2_Pyrus_IChannel $channel);
    public function update(PEAR2_Pyrus_IChannel $channel);
    public function delete(PEAR2_Pyrus_IChannel $channel);
    public function get($channel);
    public function exists($channel, $strict = true);
    public function setAlias($channel, $alias);
    public function parseName($name);
    public function parsedNameToString($name);
}