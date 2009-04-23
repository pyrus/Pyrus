<?php
/**
 * PEAR2_Pyrus_ChannelRegistry_Pear1
 *
 * PHP version 5
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Gregory Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://svn.pear.php.net/PEAR2/Pyrus
 */

/**
 * This is the central registry, that is used for all installer options,
 * stored in .reg files for PEAR 1 compatibility
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Gregory Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/PEAR2/Pyrus
 */
class PEAR2_Pyrus_ChannelRegistry_Pear1 extends PEAR2_Pyrus_ChannelRegistry_Base
{
    private $_path;
    private $_channelPath;
    private $_aliasPath;
    protected $readonly;
    function __construct($path, $readonly = false)
    {
        $this->readonly = $readonly;
        $this->_path = $path;
        $this->_channelPath = $path . DIRECTORY_SEPARATOR . '.channels';
        $this->_aliasPath = $path . DIRECTORY_SEPARATOR . '.alias';
        if (!file_exists($this->_channelPath) || !is_dir($this->_channelPath)) {
            if ($readonly) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot initialize ' .
                    'PEAR1 channel registry, directory does not exist and registry is read-only');
            }
            if (!@mkdir($this->_channelPath, 0755, true)) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot initialize ' .
                    'PEAR1 channel registry, channel directory could not be initialized');
            }
        }

        if (!file_exists($this->_aliasPath) || !is_dir($this->_aliasPath)) {
            if ($readonly) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot initialize ' .
                    'PEAR1 channel registry, aliasdirectory does not exist and registry is read-only');
            }
            if (!@mkdir($this->_aliasPath, 0755, true)) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot initialize ' .
                    'PEAR1 channel registry, channel aliasdirectory could not be initialized');
            }
        }
        if (!$this->readonly) {
            if (!$this->exists('pear.php.net')) {
                $this->initDefaultChannels();
            }
        }
    }

    private function _channelFileName($channel, $noaliases = false)
    {
        if (!$noaliases) {
            $c = $this->_channelAliasFileName($channel);
            if (file_exists($c)) {
                $channel = implode('', file($c));
            }
        }
        return $this->_channelPath . DIRECTORY_SEPARATOR . str_replace('/', '_',
            strtolower($channel)) . '.reg';
    }

    private function _channelAliasFileName($alias)
    {
        return $this->_path . DIRECTORY_SEPARATOR . '.alias' .
              DIRECTORY_SEPARATOR . str_replace('/', '_', strtolower($alias)) . '.txt';
    }

    private function _channelFromAlias($alias)
    {
        $file = $this->_channelAliasFileName($alias);
        if (file_exists($file)) {
            return file_get_contents($file);
        }
        return $alias;
    }

    public function add(PEAR2_Pyrus_IChannel $channel, $update = false, $lastmodified = false)
    {
        if (!is_writeable($this->_channelPath)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add channel ' .
                $channel->name . ', channel registry path is not writeable');
        }
        $channel->validate();
        if ($this->exists($channel->name)) {
            if (!$update) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add channel ' .
                    $channel->name . ', channel already exists, use update to change');
            }
            $checker = $this->get($channel->name);
            if ($channel->alias != $checker->alias) {
                if (file_exists($this->_channelAliasFileName($checker->alias))) {
                    @unlink($this->_channelAliasFileName($checker->alias));
                }
            }
        } elseif ($update) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Error: channel ' .
                $channel->name . ' is unknown');
        }
        if ($channel->alias != $channel->name) {
            if (file_exists($this->_channelAliasFileName($channel->alias)) &&
                  $this->_channelFromAlias($channel->alias) != $channel->name) {
                $channel->alias = $channel->name;
            }
            $fp = @fopen($this->_channelAliasFileName($channel->alias), 'w');
            if (!$fp) {
                throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add/update channel ' .
                    $channel->name . ', unable to open PEAR1 alias file');
            }
            fwrite($fp, $channel->name);
            fclose($fp);
        }
        $fp = @fopen($this->_channelFileName($channel->name), 'wb');
        if (!$fp) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot add/update channel ' .
                $channel->name . ', unable to open PEAR1 channel registry file');
        }
        $info = (string) $channel;
        $parser = new PEAR2_Pyrus_XMLParser;
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

    public function update(PEAR2_Pyrus_IChannel $channel)
    {
        return $this->add($channel, true);
    }

    public function delete(PEAR2_Pyrus_IChannel $channel)
    {
        $name = $channel->name;
        if ($name == 'pear.php.net' || $name == 'pear2.php.net' || $name == 'pecl.php.net' || $name == '__uri') {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete default channel ' .
                $channel->name);
        }
        if (!$this->exists($name)) {
            return true;
        }
        // add tests for installed packages here
        if (0) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Cannot delete channel ' .
                $name . ', packages are installed');
        }
        @unlink($this->_channelFileName($name));
        @unlink($this->_channelAliasFileName($channel->alias));
    }

    public function get($channel, $strict = true)
    {
        if (!$this->exists($channel, $strict)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Channel ' . $channel .
                ' does not exist');
        }
        $cont = file_get_contents($this->_channelFileName($channel, $strict));
        $a = @unserialize($cont);
        if (!$a || !is_array($a)) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Channel ' . $channel .
                ' PEAR1 registry file is corrupt');
        }
        try {
            $chan = new PEAR2_Pyrus_ChannelRegistry_Channel($this, $a);
            return $chan;
        } catch (Exception $e) {
            throw new PEAR2_Pyrus_ChannelRegistry_Exception('Channel ' . $channel .
                ' PEAR1 registry file is invalid channel information', $e);
        }
    }

    public function exists($channel, $strict = true)
    {
        $chan = $this->_channelFileName($channel);
        if (file_exists($chan)) {
            return true;
        }
        return false;
    }

    function listChannels()
    {
        $ret = array();
        foreach (new RegexIterator(new DirectoryIterator($this->_channelPath),
                                '/^(.+?)\.reg/', RegexIterator::GET_MATCH) as $file) {
            $ret[] = $this->get(str_replace('_', '/', $file[1]))->name;
        }
        return $ret;
    }
}
