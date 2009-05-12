<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Xml
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */

/**
 * An implementation of a Pyrus channel registry within XML files.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ChannelRegistry_Xml extends PEAR2_Pyrus_ChannelRegistry_Base
{
    protected $channelpath;
    /**
     * Initialize the registry
     *
     * @param string $path
     */
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        $this->path = $path;
        $this->channelpath = $path . DIRECTORY_SEPARATOR . '.xmlregistry' . DIRECTORY_SEPARATOR .
            'channels'; 
        if (!$this->readonly) {
            if (!$this->exists('pear.php.net')) {
                $this->initDefaultChannels();
            }
        }
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

    private function _unmung($name)
    {
        return str_replace(array('##', '###'), array('/', '\\'), $name);
    }

    /**
     * Get the filename to store a channel
     *
     * @param PEAR2_Pyrus_IChannel|string $channel Channel to save
     *
     * @return string
     */
    protected function getChannelFile($channel)
    {
        if ($channel instanceof PEAR2_Pyrus_IChannel) {
            $channel = $channel->name;
        }

        return $this->channelpath . DIRECTORY_SEPARATOR . 'channel-' .
            $this->_mung($channel) . '.xml';
    }

    /**
     * Get the filename for a channel alias.
     *
     * @param string $alias Alias to save
     *
     * @return string
     */
    protected function getAliasFile($alias)
    {
        return $this->channelpath . DIRECTORY_SEPARATOR . 'channelalias-' .
            $this->_mung($alias) . '.txt';
    }

    function channelFromAlias($alias)
    {
        if (file_exists($this->getAliasFile($alias))) {
            return file_get_contents($this->getAliasFile($alias));
        }
        return $alias;
    }

    /**
     * Check if the channel has been discovered.
     *
     * @param string $channel Name of the channel
     * @param bool   $strict  Allow aliases or not
     *
     * @return bool
     */
    function exists($channel, $strict = true)
    {
        if (file_exists($this->getChannelFile($channel))) {
            return true;
        }

        if ($strict) {
            return false;
        }

        return file_exists($this->getAliasFile($channel));
    }

    function add(PEAR2_Pyrus_IChannel $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add channel, registry is read-only');
        }

        $file = $this->getChannelFile($channel);
        if (@file_exists($file)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->alias . ' has already been discovered');
        }
        if (!@is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, (string) $channel);
        $alias = $channel->alias;
        file_put_contents($this->getAliasFile($alias), $channel->name);
    }

    function update(PEAR2_Pyrus_IChannel $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot update channel, registry is read-only');
        }

        $file = $this->getChannelFile($channel);
        if (!@file_exists($file)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->name . ' is unknown');
        }

        file_put_contents($file, (string) $channel);
        $alias = $channel->alias;
        file_put_contents($this->getAliasFile($alias), $channel->name);
    }

    function delete(PEAR2_Pyrus_IChannel $channel)
    {
        if ($this->readonly) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete channel, registry is read-only');
        }

        $name = $channel->name;
        if ($name == 'pear.php.net' || $name == 'pear2.php.net' || $name == 'pecl.php.net' || $name == '__uri') {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete default channel ' .
                $channel->name);
        }

        if ($this->packageCount($name)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete channel ' .
                $name . ', packages are installed');
        }

        @unlink($this->getChannelFile($channel));
        @unlink($this->getAliasFile($channel->alias));
    }

    function get($channel, $strict = true)
    {
        if ($this->exists($channel, $strict)) {
            $channel = $this->channelFromAlias($channel);
            $chan = new PEAR2_Pyrus_ChannelFile($this->getChannelFile($channel));
            return new PEAR2_Pyrus_ChannelRegistry_Channel($this, $chan->getArray());
        }

        throw new PEAR2_Pyrus_ChannelRegistry_Exception('Unknown channel: ' . $channel);
    }

    /**
     * List all discovered channels
     *
     * @return array
     */
    function listChannels()
    {
        $ret = array();
        foreach (new RegexIterator(new DirectoryIterator($this->channelpath),
                                '/channel-(.+?)\.xml/', RegexIterator::GET_MATCH) as $file) {
            $ret[] = $this->get($this->_unmung($file[1]))->name;
        }

        return $ret;
    }
}
