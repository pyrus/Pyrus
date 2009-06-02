<?php
/**
 * PEAR2_Pyrus_PluginRegistry
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
 * Registry manager for Pyrus plugins
 *
 * The plugin manager is a standard Pyrus registry, but also has
 * specialized commands to retrieve command plugins, custom roles/tasks, and scripts
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_PluginRegistry extends PEAR2_Pyrus_Registry
{
    protected $pluginRegistryPath;
    static protected $config;
    static protected $commandMap = array();
    static protected $autoloadMap = array();
    static protected $frontend;

    function __construct($path = null)
    {
        if ($path === null) {
            $this->pluginRegistryPath = PEAR2_Pyrus_Config::current()->plugins_dir;
        } else {
            $this->pluginRegistryPath = $path;
        }
        $current = PEAR2_Pyrus_Config::current();
        self::$config = PEAR2_Pyrus_Config::singleton($this->pluginRegistryPath);
        PEAR2_Pyrus_Config::setCurrent($current->path);
        parent::__construct($this->pluginRegistryPath, array('Sqlite3', 'Xml'));
    }

    /**
     * Scan the plugin registry for custom roles, tasks and commands,
     * and register them as existing.
     */
    function scan()
    {
        static $scanned = false;
        if ($scanned) {
            return;
        }
        $scanned = true;
        $parser = new PEAR2_Pyrus_XMLParser;
        $schemapath = PEAR2_Pyrus::getDataPath();
        if (!file_exists(PEAR2_Pyrus::getDataPath() . '/channel-1.0.xsd')) {
            $schemapath = realpath(__DIR__ . '/../../data');
        }
        $roleschema = $schemapath . '/customrole-2.0.xsd';
        $taskschema = $schemapath . '/customtask-2.0.xsd';
        $commandschema = $schemapath . '/customcommand-2.0.xsd';

        try {
            foreach (PEAR2_Pyrus_Config::current()->channelregistry as $channel) {
                foreach ($this->listPackages($channel->name) as $package) {
                    $chan = $channel->name;
                    $files = $this->info($package, $chan, 'installedfiles');
                    // each package may only have 1 role, task or command
                    foreach ($files as $path => $info) {
                        switch ($info['role']) {
                            case 'customrole' :
                                $roleinfo = $parser->parse($path, $roleschema);
                                $roleinfo = $roleinfo['role'];
                                static::makeAutoloader($roleinfo, 'role');
                                PEAR2_Pyrus_Installer_Role::registerCustomRole($roleinfo);
                                continue 2;
                            case 'customtask' :
                                $taskinfo = $parser->parse($path, $taskschema);
                                $taskinfo = $taskinfo['task'];
                                static::makeAutoloader($taskinfo, 'task');
                                PEAR2_Pyrus_PackageFile_v2::registerCustomTask($taskinfo);
                                continue 2;
                            case 'customcommand' :
                                $commands = $parser->parse($path, $commandschema);
                                $this->addCommand($commands['commands']['command']);
                                continue 2;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            PEAR2_Pyrus_Log::log(0, 'Unable to add all custom roles/tasks/commands: ' . $e);
        }
    }

    static function registerFrontend($frontend)
    {
        self::$frontend = $frontend;
    }

    static function addCommand($commands)
    {
        if (!isset($commands[0])) {
            $commands = array($commands);
        }
        foreach ($commands as $command) {
            if (isset(self::$commandMap[$command['name']])) {
                throw new PEAR2_Pyrus_PluginRegistry_Exception($command['name'] . ' is' .
                                                               ' already mapped and cannot be re-used');
            }
            self::$commandMap[$command['name']] = $command;
            static::makeAutoloader($command, 'command');
            if (null !== self::$frontend) {
                self::$frontend->mapCommand($command);
            }
        }
    }

    static function getCommandInfo($command = null)
    {
        if (null === $command) {
            return self::$commandMap;
        }
        if (isset(self::$commandMap[$command])) {
            return self::$commandMap[$command];
        }
        return false;    
    }

    static function makeAutoloader($info, $type)
    {
        if (isset($info['autoloadpath']) && !isset(self::$autoloadMap[$info['autoloadpath']])) {
            $fullpath = realpath(self::$config->php_dir . DIRECTORY_SEPARATOR .
                $info['autoloadpath']);
            if (!$fullpath) {
                throw new PEAR2_Pyrus_PluginRegistry_Exception(
                    'Unable to create autoloader for custom ' . $type . $info['name'] .
                    ', autoload path ' . $info['autoloadpath'] . ' does not exist');
            }
            $autoloader = function($class) use ($fullpath) {
                if (file_exists($fullpath . '/' . str_replace('_', '/', $class) . '.php')) {
                    include $fullpath . '/' . str_replace('_', '/', $class) . '.php';
                }
            };
            spl_autoload_register($autoloader);
        }
    }
}
