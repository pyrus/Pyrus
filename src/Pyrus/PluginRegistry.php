<?php
/**
 * \Pyrus\PluginRegistry
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * Registry manager for Pyrus plugins
 *
 * The plugin manager is a standard Pyrus registry, but also has
 * specialized commands to retrieve command plugins, custom roles/tasks, and scripts
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus;
class PluginRegistry extends \Pyrus\Registry
{
    protected $path;
    static protected $config;
    static protected $commandMap = array();
    static protected $autoloadMap = array();
    static protected $frontend;

    function __construct($path = null)
    {
        $this->path = $path === null ? Config::current()->plugins_dir : $path;

        $current = Config::current();
        self::$config = Config::singleton($this->path);
        Config::setCurrent($current->path);
        parent::__construct($this->path, array('Sqlite3', 'Xml'));
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
        $parser = new XMLParser;
        $schemapath = Main::getDataPath();

        $roleschema    = $schemapath . '/customrole-2.0.xsd';
        $taskschema    = $schemapath . '/customtask-2.0.xsd';
        $commandschema = $schemapath . '/customcommand-2.0.xsd';

        try {
            foreach (Config::current()->channelregistry as $channel) {
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
                                Installer\Role::registerCustomRole($roleinfo);
                                continue 2;
                            case 'customtask' :
                                $taskinfo = $parser->parse($path, $taskschema);
                                $taskinfo = $taskinfo['task'];
                                static::makeAutoloader($taskinfo, 'task');
                                Task\Common::registerCustomTask($taskinfo);
                                continue 2;
                            case 'customcommand' :
                                $commands = $parser->parse($path, $commandschema);
                                $this->addCommand($commands['commands']['command']);
                                continue 2;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Logger::log(0, 'Unable to add all custom roles/tasks/commands: ' . $e);
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
                throw new PluginRegistry\Exception($command['name'] . ' is' .
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
        $autoloadPath = isset($info['autoloadpath']) ? $info['autoloadpath'] : false;
        if ($autoloadPath === false) {
            if(isset($info['class']) && self::$config !== null) {
                $autoloadPath = self::$config->php_dir;
                $fullPath = self::$config->php_dir;
            } else {
                return;
            }
        } else {
            if (self::$config == null) {
                // We have no known config, running from dev
                return;
            }
            $fullPath = realpath(self::$config->php_dir . DIRECTORY_SEPARATOR . $autoloadPath);
        }

        if (isset(self::$autoloadMap[$autoloadPath])) {
            return;
        }

        if (!$fullPath) {
            throw new PluginRegistry\Exception(
                'Unable to create autoloader for custom ' . $type . ' ' . $info['name'] .
                ', autoload path ' . $autoloadPath . ' does not exist');
        }

        $autoloader = function($class) use ($fullPath) {
            $filePath = $fullPath . '/' . str_replace(array('\\', '_'), '/', $class) . '.php';
            if (file_exists($filePath)) {
                include $filePath;
            }
        };
        spl_autoload_register($autoloader);
    }
}
