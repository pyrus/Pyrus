<?php

class PEAR2_Pyrus_ChannelRegistry_Xml extends PEAR2_Pyrus_ChannelRegistry_Base
{
    private $_path;

    /**
     * Initialize the registry
     *
     * @param string $path
     */
    function __construct($path)
    {
        $this->_path = $path;
    }

    /**
     * Convert a name into a path-friendly name
     *
     * @param string $name
     */
    private function _mung($name)
    {
        return str_replace(array('/', '\\'), array('##', '###'), $name);
    }

    private function _getChannelFile($channel)
    {
        if ($channel instanceof PEAR2_Pyrus_IChannel) {
            $channel = $channel->getName();
        }
        return $this->_path . DIRECTORY_SEPARATOR . 'channel-' .
            $this->_mung($channel) . '.xml';
    }

    private function _getAliasFile($alias)
    {
        return $this->_path . DIRECTORY_SEPARATOR . 'channelalias-' .
            $this->_mung($alias) . '.txt';
    }

    function exists($channel, $strict = true)
    {
        if (file_exists($this->_getChannelFile($channel))) {
            return true;
        }
        if ($strict) {
            return false;
        }
        return file_exists($this->_getAliasFile($alias));
    }

    function add(PEAR2_Pyrus_IChannel $channel)
    {
        $file = $this->_getChannelFile($channel);
        if (@file_exists($file)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->getName() . ' has already been discovered');
        }
        file_put_contents($file, (string) $channel);
        $alias = $channel->getAlias();
        file_put_contents($this->_getAliasFile($alias), $channel->getName());
    }

    function update(PEAR2_Pyrus_IChannel $channel)
    {
        $file = $this->_getChannelFile($channel);
        if (!@file_exists($file)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->getName() . ' is unknown');
        }
        file_put_contents($file, (string) $channel);
        $alias = $channel->getAlias();
        file_put_contents($this->_getAliasFile($alias), $channel->getName());
    }

    function delete(PEAR2_Pyrus_IChannel $channel)
    {
        @unlink($this->_getChannelFile($channel));
        @unlink($this->_getAliasFile($channel->getAlias()));
    }

    function get($channel)
    {
        if ($this->exists($channel)) {
            $data = @file_get_contents($this->_getChannelFile($channel));
            return new PEAR2_Pyrus_ChannelRegistry_Channel_Xml($this, $data);
        } else {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
        }
    }

    function __get($value)
 	{
 	    switch ($value) {
 	        case 'mirrors' :
 	            if (!isset($this->_channelInfo['servers']['mirror'][0])) {
 	                return array(new PEAR2_Pyrus_Channel_Mirror(
 	                              $this->_channelInfo['servers']['mirror'], $this,
                                  $this->_parent));
 	            }
 	            $ret = array();
 	            foreach ($this->_channelInfo['servers']['mirror'] as $i => $mir) {
 	                $ret[$mir['attribs']['host']] = new PEAR2_Pyrus_Channel_Mirror(
 	                      $this->_channelInfo['servers']['mirror'][$i], $this,
 	                      $this->_parent);
                }
                return $ret;
 	    }
 	}

 	function setAlias($channel, $alias)
 	{
        if (!$this->exists($channel)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
        }
        $channel = $this->get($channel);
        @unlink($this->_getAliasFile($channel->getAlias()));
        file_put_contents($this->_getAliasFile($alias), $channel->getName());
 	}

 	function listChannels()
 	{

 	    foreach (new RegexIterator(new DirectoryIterator($path),
 	                               '/channel-(.+?)\.xml/', RegexIterator::GET_MATCH) as $file) {
            
        }
 	}
}