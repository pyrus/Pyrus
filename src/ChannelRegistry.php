<?php

class PEAR2_Pyrus_ChannelRegistry implements ArrayAccess
{
    static private $_allRegistries = array();
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

    static public function singleton($path, $registries = array('Sqlite', 'Xml'))
    {
        if (!isset(self::$_allRegistries[$path])) {
            self::$_allRegistries[$path] = new PEAR2_Pyrus_ChannelRegistry($path);
        }
        return self::$_allRegistries[$path];
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
}