<?php
/**
 * PEAR2_Pyrus_ChannelRegistry
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
class PEAR2_Pyrus_ChannelRegistry implements ArrayAccess, IteratorAggregate, PEAR2_Pyrus_IChannelRegistry
{
    /**
     * Class to instantiate for singleton.
     *
     * This is useful for unit-testing and for extending the registry
     * @var string
     */
    static public $className = 'PEAR2_Pyrus_ChannelRegistry';
    static private $_allRegistries = array();
    /**
     * The parent registry
     *
     * This is used to implement cascading registries
     * @var PEAR2_Pyrus_ChannelRegistry
     */
    protected $parent;
    private $_registries = array();

    protected function __construct($path, $registries = array('Sqlite', 'Xml'))
    {
        $exceptions = array();
        foreach ($registries as $registry) {
            try {
                $registry = ucfirst($registry);
                $registry = "PEAR2_Pyrus_ChannelRegistry_$registry";
                if (!class_exists($registry, true)) {
                    $exceptions[] = new PEAR2_Pyrus_ChannelRegistry_Exception(
                        'Unknown channel registry type: ' . $registry);
                    continue;
                }
                $this->_registries[] = new $registry($path);
            } catch (Exception $e) {
                $exceptions[] = $e;
            }
        }
        if (!count($this->_registries)) {
            throw new PEAR2_Pyrus_Registry_Exception(
                'Unable to initialize registry for path "' . $path . '"',
                $exceptions);
        }
    }

    public function setParent(PEAR2_Pyrus_ChannelRegistry $parent = null)
    {
        $this->parent = $parent;
    }

    static public function singleton($path, $registries = array('Sqlite', 'Xml'))
    {
        if (!isset(self::$_allRegistries[$path])) {
            $a = self::$className;
            self::$_allRegistries[$path] = new $a($path);
        }
        return self::$_allRegistries[$path];
    }

    public function add(PEAR2_Pyrus_IChannel $channel)
    {
        foreach ($this->_registries as $reg) {
            $reg->add($channel);
        }
    }

    public function update(PEAR2_Pyrus_IChannel $channel)
    {
        foreach ($this->_registries as $reg) {
            $reg->update($channel);
        }
    }

    public function delete(PEAR2_Pyrus_IChannel $channel)
    {
        foreach ($this->_registries as $reg) {
            $reg->delete($channel);
        }
    }

    public function get($channel)
    {
        return $this->_registries[0]->get($channel);
    }

    public function exists($channel, $strict = true)
    {
        return $this->_registries[0]->exists($channel, $strict);
    }

    public function parseName($name)
    {
        return $this->_registries[0]->parseName($name);
    }

    public function parsedNameToString($name)
    {
        return $this->_registries[0]->parsedNameToString($name);
    }

    public function listChannels()
    {
        return $this->_registries[0]->listChannels();
    }

    public function offsetGet($offset)
    {
        return $this->_registries[0]->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        foreach ($this->_registries as $reg) {
            $reg->add($offset, $value);
        }
    }

    public function offsetExists($offset)
    {
        return $this->_registries[0]->exists($offset);
    }

    public function offsetUnset($offset)
    {
        foreach ($this->_registries as $reg) {
            $reg->delete($offset);
        }
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->_registries[0], $method), $args);
    }

    /**
     * Parse a string to determine which package file is requested
     *
     * This differentiates between the three kinds of packages:
     *
     *  - local files
     *  - remote static URLs
     *  - dynamic abstract package names
     * @param string $pname
     * @return string|array A string is returned if this is a file, otherwise an array
     *                      containing information is returned
     */
    static public function parsePackageName($pname, $assumeabstract = false)
    {
        if (!$assumeabstract && @file_exists($pname) && @is_file($pname)) {
            return $pname;
        }
        if (!count(self::$_allRegistries)) {
            $registry = new PEAR2_Pyrus_ChannelRegistry_Sqlite(false);
        } else {
            foreach (self::$_allRegistries as $registry) {
                try {
                    return $registry->parseName($pname);
                } catch (Exception $e) {
                    // next
                }
            }
        }
        return $registry->parseName($pname);
    }

    static public function parsedPackageNameToString($name)
    {
        if (!count(self::$_allRegistries)) {
            $registry = new PEAR2_Pyrus_ChannelRegistry_Sqlite(false);
        } else {
            foreach (self::$_allRegistries as $registry) {
                try {
                    return $registry->parsedNameToString($name);
                } catch (Exception $e) {
                    // next
                }
            }
        }
        return $registry->parsedNameToString($name);
    }

    public function getIterator()
    {
        return $this->_registries[0];
    }
}