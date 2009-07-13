<?php
/**
 * \pear2\Pyrus\ChannelRegistry
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
 * Base class for Pyrus.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
namespace pear2\Pyrus;
class ChannelRegistry implements \ArrayAccess, \IteratorAggregate, \pear2\Pyrus\IChannelRegistry
{
    /**
     * Class to instantiate for singleton.
     *
     * This is useful for unit-testing and for extending the registry
     * @var string
     */
    static public $className = 'pear2\Pyrus\ChannelRegistry';
    /**
     * The parent registry
     *
     * This is used to implement cascading registries
     * @var \pear2\Pyrus\ChannelRegistry
     */
    protected $parent;
    protected $path;
    protected $readonly;
    private $_registries = array();

    public function __construct($path, $registries = array('Sqlite3', 'Xml'), $readonly = false)
    {
        $this->path = $path;
        $this->readonly = $readonly;
        $exceptions = array();
        foreach ($registries as $registry) {
            try {
                $registry = ucfirst($registry);
                $registry = 'pear2\Pyrus\ChannelRegistry\\' . $registry;
                if (!class_exists($registry, true)) {
                    $exceptions[] = new \pear2\Pyrus\ChannelRegistry\Exception(
                        'Unknown channel registry type: ' . $registry);
                    continue;
                }
                $this->_registries[] = new $registry($path, $readonly);
            } catch (\pear2\Pyrus\ChannelRegistry\Exception $e) {
                $exceptions[] = $e;
            } catch (\pear2\Pyrus\Registry\Exception $e) {
                $exceptions[] = $e;
            }
        }
        if (!count($this->_registries)) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception(
                'Unable to initialize registry for path "' . $path . '"',
                $exceptions);
        }
    }

    public function setParent(\pear2\Pyrus\ChannelRegistry $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * Add a channel to the registry.
     *
     * @param \pear2\Pyrus\IChannel $channel Channel to add.
     */
    public function add(\pear2\Pyrus\IChannel $channel, $update = false, $lastmodified = false)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot add channel, registry is read-only');
        }
        foreach ($this->_registries as $reg) {
            $reg->add($channel, $update, $lastmodified);
        }
    }

    public function update(\pear2\Pyrus\IChannel $channel)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot update channel, registry is read-only');
        }
        foreach ($this->_registries as $reg) {
            $reg->update($channel);
        }
    }

    public function delete(\pear2\Pyrus\IChannel $channel)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot delete channel, registry is read-only');
        }
        if (in_array($channel->name, $this->_registries[0]->getDefaultChannels())) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot delete default channel ' .
                $channel->name);
        }
        foreach ($this->_registries as $reg) {
            $reg->delete($channel);
        }
    }

    public function get($channel, $strict = true)
    {
        try {
            return $this->_registries[0]->get($channel, $strict);
        } catch (\Exception $e) {
            // don't fail on the default channels, these should always exist
            switch ($channel) {
                case 'pear.php.net' :
                    return $this->_registries[0]->getPearChannel();
                case 'pear2.php.net' :
                    return $this->_registries[0]->getPear2Channel();
                case 'pecl.php.net' :
                    return $this->_registries[0]->getPeclChannel();
                case 'doc.php.net' :
                    return $this->_registries[0]->getDocChannel();
                case '__uri' :
                    return $this->_registries[0]->getUriChannel();
            }
            throw $e;
        }
    }

    /**
     * Check if channel has been discovered and in the registry.
     *
     * @param string $channel Channel name or alias: pear.php.net, pear
     * @param bool   $strict  Do not check aliases.
     * 
     * @return bool
     */
    public function exists($channel, $strict = true)
    {
        return $this->_registries[0]->exists($channel, $strict);
    }

    public function parseName($name, $defaultChannel = 'pear2.php.net')
    {
        foreach ($this->_registries as $reg) {
            try {
                return $reg->parseName($name, $defaultChannel);
            } catch (\Exception $e) {
                continue;
            }
        }
        if ($this->parent) {
            return $this->parent->parseName($name, $defaultChannel);
        }
        // recycle last exception
        throw new \pear2\Pyrus\ChannelRegistry\Exception('Unable to process package name', $e);
    }

    public function parsedNameToString($name, $brief = false)
    {
        return $this->_registries[0]->parsedNameToString($name, $brief);
    }

    public function listChannels()
    {
        return $this->_registries[0]->listChannels();
    }

    public function offsetGet($offset)
    {
        return $this->get($offset, false);
    }

    public function offsetSet($offset, $value)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot add channel, registry is read-only');
        }
        if ($value instanceof \pear2\Pyrus\IChannelFile) {
            $value = new \pear2\Pyrus\Channel($value);
        }
        foreach ($this->_registries as $reg) {
            $reg->add($value);
        }
    }

    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    public function offsetUnset($offset)
    {
        if ($this->readonly) {
            throw new \pear2\Pyrus\ChannelRegistry\Exception('Cannot delete channel, registry is read-only');
        }
        $chan = $this->get($offset, false);
        foreach ($this->_registries as $reg) {
            $reg->delete($chan);
        }
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_registries[0], $method), $args);
    }

    public function getIterator()
    {
        return $this->_registries[0];
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function getPath()
    {
        return $this->path;
    }
}
