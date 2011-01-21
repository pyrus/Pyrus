<?php
/**
 * \PEAR2\Pyrus\PluginRegistry
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
 * Registry manager for Pyrus plugins
 *
 * The plugin manager is a standard Pyrus registry, but also has
 * specialized commands to retrieve command plugins, custom roles/tasks, and scripts
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.php.net/viewvc/pear2/Pyrus/
 */
namespace PEAR2\Pyrus;
class PluginRegistry extends \PEAR2\Pyrus\Registry
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
        if (!file_exists(Main::getDataPath() . '/channel-1.0.xsd')) {
            $schemapath = realpath(__DIR__ . '/../../data');
        }

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
        if ($autoloadPath === false && isset($info['class'])) {
            $info['autoloadpath'] = realpath(self::$config->php_dir);
        }

        if ($autoloadPath === false || isset(self::$autoloadMap[$info['autoloadpath']])) {
            return;
        }
        
        $fullpath = realpath(self::$config->php_dir . DIRECTORY_SEPARATOR . $info['autoloadpath']);
        if (!$fullpath) {
            throw new PluginRegistry\Exception(
                'Unable to create autoloader for custom ' . $type . ' ' . $info['name'] .
                ', autoload path ' . $info['autoloadpath'] . ' does not exist');
        }

        $autoloader = function($class) use ($fullpath) {
            $filepath = $fullpath . '/' . str_replace(array('\\', '_'), '/', $class) . '.php';
            if (file_exists($filepath)) {
                include $filepath;
            }
        };
        spl_autoload_register($autoloader);
    }
}
