<?php
/**
 * \PEAR2\Pyrus\ChannelRegistry\Xml
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */

/**
 * An implementation of a Pyrus channel registry within XML files.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus\ChannelRegistry;
use \PEAR2\Pyrus\Main as Main;
class Xml extends Base
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
        if (isset(Main::$options['packagingroot'])) {
            $path = Main::prepend(Main::$options['packagingroot'], $path);
        }
        $this->path = $path;
        $this->channelpath = $path . DIRECTORY_SEPARATOR . '.xmlregistry' . DIRECTORY_SEPARATOR .
            'channels';
        if (1 === $this->exists('pear.php.net')) {
            $this->initialized = false;
        } else {
            $this->initialized = true;
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
     * @param \PEAR2\Pyrus\ChannelInterface|string $channel Channel to save
     *
     * @return string
     */
    protected function getChannelFile($channel)
    {
        if ($channel instanceof \PEAR2\Pyrus\ChannelInterface) {
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
        if (!$this->initialized) {
            return parent::channelFromAlias($alias);
        }
        if (file_exists($this->getAliasFile($alias))) {
            return file_get_contents($this->getAliasFile($alias));
        }
        if (file_exists($this->getChannelFile($alias))) {
            return $alias;
        }
        throw new Exception('Unknown channel/alias: ' . $alias);
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
            return parent::exists($channel, $strict);
        }

        if (file_exists($this->getAliasFile($channel))) {
            return true;
        }
        return parent::exists($channel, $strict);
    }

    function add(\PEAR2\Pyrus\ChannelInterface $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new Exception('Cannot add channel, registry is read-only');
        }

        $this->lazyInit();

        $file = $this->getChannelFile($channel);
        if (@file_exists($file)) {
            throw new Exception('Error: channel ' .$channel->name . ' has already been discovered');
        }
        if (!@is_dir(dirname($file))) {
            mkdir(dirname($file), 0755, true);
        }

        file_put_contents($file, (string) $channel);
        $alias = $channel->alias;
        file_put_contents($this->getAliasFile($alias), $channel->name);
    }

    function update(\PEAR2\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot update channel, registry is read-only');
        }

        $this->lazyInit();

        $file = $this->getChannelFile($channel);
        if (!@file_exists($file)) {
            throw new Exception('Error: channel ' . $channel->name . ' is unknown');
        }

        file_put_contents($file, (string) $channel);
        $alias = $channel->alias;
        file_put_contents($this->getAliasFile($alias), $channel->name);
    }

    function delete(\PEAR2\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot delete channel, registry is read-only');
        }

        $name = $channel->name;
        if (in_array($name, $this->getDefaultChannels())) {
            throw new Exception('Cannot delete default channel ' . $channel->name);
        }

        $this->lazyInit();

        if (!$this->exists($name)) {
            return true;
        }

        if ($this->packageCount($name)) {
            throw new Exception('Cannot delete channel ' . $name . ', packages are installed');
        }

        @unlink($this->getChannelFile($channel));
        @unlink($this->getAliasFile($channel->alias));
    }

    function get($channel, $strict = true)
    {
        $exists = $this->exists($channel, $strict);
        if ($exists) {
            $channel = $this->channelFromAlias($channel);
            if (1 === $exists) {
                return $this->getDefaultChannel($channel);
            } else {
                $chan = new \PEAR2\Pyrus\ChannelFile($this->getChannelFile($channel));
            }
            return new Channel($this, $chan->getArray());
        }

        throw new Exception('Unknown channel: ' . $channel);
    }

    /**
     * List all discovered channels
     *
     * @return array
     */
    function listChannels()
    {
        if (!$this->initialized) {
            return $this->getDefaultChannels();
        }
        $ret = array();
        foreach (new \RegexIterator(new \DirectoryIterator($this->channelpath),
                                '/channel-(.+?)\.xml/', \RegexIterator::GET_MATCH) as $file) {
            $ret[] = $this->get($this->_unmung($file[1]))->name;
        }

        return $ret;
    }
}
