<?php
function __autoload($class)
{
    require '\\development\\pyrus\\trunk\\' . str_replace(array('PEAR2_Pyrus_', '_'), array('', '\\'), $class) . '.php';
}
class PEAR2_Pyrus_ChannelRegistry implements PEAR2_Pyrus_IChannelRegistry
{
    static private $_registries = array();

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
                self::$_registries[] = new $registry($path);
            } catch (Exception $e) {
                $exceptions[] = $e;
            }
        }
        if (!count(self::$_registries)) {
            throw new PEAR2_Pyrus_Registry_Exception(
                'Unable to initialize registry for path "' . $path . '"',
                $exceptions);
        }
    }

    static public function singleton($path)
    {
        if (!isset(self::$_registries[$path])) {
            self::$_registries[$path] = new PEAR2_Pyrus_ChannelRegistry($path);
        }
        return self::$_registries[$path];
    }

    public function add(PEAR2_Pyrus_ChannelFile $channel)
    {
        foreach (self::$_registries as $reg) {
            $reg->add($channel);
        }
    }

    public function update(PEAR2_Pyrus_ChannelFile $channel)
    {
        foreach (self::$_registries as $reg) {
            $reg->update($channel);
        }
    }

    public function delete($channel)
    {
        foreach (self::$_registries as $reg) {
            $reg->delete($channel);
        }
    }

    public function getAlias($channel)
    {
        return self::$_registries[0]->getAlias($channel);
    }

    public function getObject($channel)
    {
        return self::$_registries[0]->getObject($channel);
    }

    public function exists($channel, $strict = true)
    {
        return self::$_registries[0]->exists($channel);
    }

    public function hasMirror($channel, $mirror)
    {
        return self::$_registries[0]->hasMirror($channel, $mirror);
    }

    public function setAlias($channel, $alias)
    {
        return self::$_registries[0]->hasMirror($channel, $alias);
    }

    public function getMirrors($channel)
    {
        return self::$_registries[0]->getMirrors($channel);
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
        if (!count(self::$_registries)) {
            $registry = new PEAR2_Pyrus_ChannelRegistry_Sqlite(false);
        } else {
            foreach (self::$_registries as $registry) {
                try {
                    return $registry->parseName($pname);
                } catch (Exception $e) {
                    // next
                }
            }
        }
        return $registry->parseName($pname);
    }

    public function parseName($pname)
    {
        return self::parsePackageName($pname);
    }

    public function parsedNameToString($name)
    {
        return self::parsedPackageNameToString($name);
    }

    static public function parsedPackageNameToString($name)
    {
        if (!count(self::$_registries)) {
            $registry = new PEAR2_Pyrus_ChannelRegistry_Sqlite(false);
        } else {
            foreach (self::$_registries as $registry) {
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