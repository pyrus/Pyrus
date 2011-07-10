<?php
/**
 * \Pyrus\ChannelRegistry\Pear1
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Gregory Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/PEAR2/Pyrus
 */

/**
 * This is the central registry, that is used for all installer options,
 * stored in .reg files for PEAR 1 compatibility
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Gregory Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus
 */
namespace Pyrus\ChannelRegistry;
class Pear1 extends Base
{
    private $_channelPath;
    private $_aliasPath;
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        if (!file_exists($path . '/.registry') && basename($path) !== 'php') {
            $path = $path . DIRECTORY_SEPARATOR . 'php';
        }

        $this->path = $path;
        if (isset(\Pyrus\Main::$options['packagingroot'])) {
            $path = \Pyrus\Main::prepend(\Pyrus\Main::$options['packagingroot'], $path);
        }

        $this->_channelPath = $path . DIRECTORY_SEPARATOR . '.channels';
        $this->_aliasPath   = $this->_channelPath . DIRECTORY_SEPARATOR . '.alias';
        if (!file_exists($this->_channelPath) || !is_dir($this->_channelPath)) {
            if ($readonly) {
                throw new Exception('Cannot initialize PEAR1 channel registry, directory' .
                                    ' does not exist and registry is read-only');
            }

            if (!@mkdir($this->_channelPath, 0755, true)) {
                throw new Exception('Cannot initialize PEAR1 channel registry, channel' .
                                    ' directory could not be initialized');
            }
        }

        if (!file_exists($this->_aliasPath) || !is_dir($this->_aliasPath)) {
            if ($readonly) {
                throw new Exception('Cannot initialize PEAR1 channel registry, aliasdirectory ' .
                                    'does not exist and registry is read-only');
            }

            if (!@mkdir($this->_aliasPath, 0755, true)) {
                throw new Exception('Cannot initialize PEAR1 channel registry, channel ' .
                                    'aliasdirectory could not be initialized');
            }
        }

        if (1 === $this->exists('pear.php.net')) {
            $this->initialized = false;
        } else {
            $this->initialized = true;
        }
    }

    protected function channelFileName($channel)
    {
        return $this->_channelPath . DIRECTORY_SEPARATOR . str_replace('/', '_',
            strtolower($channel)) . '.reg';
    }

    protected function channelAliasFileName($alias)
    {
        return $this->_channelPath . DIRECTORY_SEPARATOR . '.alias' .
              DIRECTORY_SEPARATOR . str_replace('/', '_', strtolower($alias)) . '.txt';
    }

    public function channelFromAlias($alias)
    {
        if (!$this->initialized) {
            return parent::channelFromAlias($alias);
        }

        $file = $this->channelAliasFileName($alias);
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        return $alias;
    }

    public function add(\Pyrus\ChannelInterface $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new Exception('Cannot add channel, registry is read-only');
        }

        if (!is_writable($this->_channelPath)) {
            throw new Exception('Cannot add channel ' . $channel->name .
                                ', channel registry path is not writable');
        }

        $this->lazyInit();

        $channel->validate();
        $exists = $this->exists($channel->name);
        if ($exists && 1 !== $exists) {
            if (!$update) {
                throw new Exception('Cannot add channel ' . $channel->name .
                                    ', channel already exists, use update to change');
            }

            $checker = $this->get($channel->name);
            if ($channel->alias != $checker->alias) {
                if (file_exists($this->channelAliasFileName($checker->alias))) {
                    @unlink($this->channelAliasFileName($checker->alias));
                }
            }
        } elseif ($update) {
            throw new Exception('Error: channel ' . $channel->name . ' is unknown');
        }

        if ($channel->alias != $channel->name) {
            if (file_exists($this->channelAliasFileName($channel->alias)) &&
                  $this->channelFromAlias($channel->alias) != $channel->name) {
                $channel->alias = $channel->name;
            }

            $fp = @fopen($this->channelAliasFileName($channel->alias), 'w');
            if (!$fp) {
                throw new Exception('Cannot add/update channel ' . $channel->name .
                                    ', unable to open PEAR1 channel alias file');
            }

            fwrite($fp, $channel->name);
            fclose($fp);
        }

        $fp = @fopen($this->channelFileName($channel->name), 'wb');
        if (!$fp) {
            throw new Exception('Cannot add/update channel ' . $channel->name .
                                ', unable to open PEAR1 channel registry file');
        }

        $info = (string) $channel;
        $parser = new \Pyrus\XMLParser;
        $info = $parser->parseString($info);
        $info = $info['channel'];
        if ($lastmodified) {
            $info['_lastmodified'] = $lastmodified;
        } else {
            $info['_lastmodified'] = date('r');
        }

        fwrite($fp, serialize($info));
        fclose($fp);
        return true;
    }

    public function update(\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot update channel, registry is read-only');
        }

        return $this->add($channel, true);
    }

    public function delete(\Pyrus\ChannelInterface $channel)
    {
        if ($this->readonly) {
            throw new Exception('Cannot delete channel, registry is read-only');
        }

        $name = $channel->name;
        if (in_array($name, $this->getDefaultChannels())) {
            throw new Exception('Cannot delete default channel ' . $channel->name);
        }

        if (!$this->exists($name)) {
            return true;
        }

        $this->lazyInit();

        if ($this->packageCount($name)) {
            throw new Exception('Cannot delete channel ' . $name . ', packages are installed');
        }

        @unlink($this->channelFileName($name));
        @unlink($this->channelAliasFileName($channel->alias));
    }

    public function get($channel, $strict = true)
    {
        $exists = $this->exists($channel, $strict);
        if (!$exists) {
            throw new Exception('Channel ' . $channel . ' does not exist');
        }

        if (1 === $exists) {
            // is a default channel not installed
            return $this->getDefaultChannel($channel);
        }

        $channel = $this->channelFromAlias($channel);
        $cont = file_get_contents($this->channelFileName($channel));
        $a = @unserialize($cont);
        if (!$a || !is_array($a)) {
            throw new Exception('Channel ' . $channel . ' PEAR1 registry file is corrupt');
        }

        try {
            $chan = new Channel($this, $a);
            if ($channel != '__uri') {
                $chan->validate();
            }

            return $chan;
        } catch (\Exception $e) {
            throw new Exception('Channel ' . $channel . ' PEAR1 registry file is invalid channel information', $e);
        }
    }

    public function exists($channel, $strict = true)
    {
        if (!$strict) {
            $channel = $this->channelFromAlias($channel);
        }

        $chan = $this->channelFileName($channel);
        if (file_exists($chan)) {
            return true;
        }

        return parent::exists($channel, $strict);
    }

    function listChannels()
    {
        if (!$this->initialized) {
            return $this->getDefaultChannels();
        }

        $ret = array();
        foreach (new \RegexIterator(new \DirectoryIterator($this->_channelPath),
                                '/^(.+?)\.reg/', \RegexIterator::GET_MATCH) as $file) {
            if ($file[1] == '__uri') {
                $ret[] = '__uri';
            } else {
                $ret[] = $this->get(str_replace('_', '/', $file[1]))->name;
            }
        }

        return $ret;
    }
}
