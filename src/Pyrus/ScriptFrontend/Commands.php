<?php
/**
 * This script handles the command line interface commands to Pyrus
 *
 * PHP version 5
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      https://github.com/pyrus/Pyrus
 */

/**
 * This script handles the command line interface commands to Pyrus
 *
 * Each command is a separate method, and will be called with the arguments
 * entered by the end user.
 *
 * @category  Pyrus
 * @package   Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2010 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      https://github.com/pyrus/Pyrus
 */
namespace Pyrus\ScriptFrontend;
class Commands implements \Pyrus\LogInterface
{
    public $commands = array();
    // for unit-testing ease
    public static $configclass = 'Pyrus\Config';
    protected $verbose;
    protected $term = array(
        'bold'   => '',
        'normal' => '',
    );
    /**
     * The actual scriptfrontend
     *
     * @var \Pyrus\ScriptFrontend
     */
    protected static $commandParser;

    function exceptionHandler($e)
    {
        if ($this->verbose > 3) {
            echo $e;
        }
        if ($e instanceof \PEAR2\Exception) {
            $causes = array();
            $e->getCauseMessage($causes);
            $causeMsg = '';
            foreach ($causes as $i => $cause) {
                $causeMsg .= str_repeat(' ', $i) . $cause['class'] . ': '
                       . $cause['message'] . "\n";
            }
            echo $causeMsg;
        } else {
            echo $e->getMessage(), "\n";
        }
    }

    function __construct($debugging = false)
    {
        if (!$debugging) {
            set_exception_handler(array($this, 'exceptionHandler'));
        }

        \Pyrus\Logger::attach($this);
        if (!isset(static::$commandParser)) {
            $schemapath = \Pyrus\Main::getDataPath() . '/customcommand-2.0.xsd';
            $defaultcommands = \Pyrus\Main::getDataPath() . '/built-in-commands.xml';
            if (!file_exists($schemapath)) {
                $schemapath = realpath(__DIR__ . '/../../../data/customcommand-2.0.xsd');
                $defaultcommands = realpath(__DIR__ . '/../../../data/built-in-commands.xml');
            }

            $parser = new \Pyrus\XMLParser;
            $commands = $parser->parse($defaultcommands, $schemapath);
            $commands = $commands['commands']['command'];
            if ('@PACKAGE_VERSION@' == '@'.'PACKAGE_VERSION@') {
                $version = '2.0.0a4'; // running from svn
            } else {
                $version = '@PACKAGE_VERSION@';
            }
            static::$commandParser = new \Pyrus\ScriptFrontend(array(
                    'version' => $version,
                    'description' => 'Pyrus, the installer for PEAR2',
                    'name' => 'php ' . basename($_SERVER['argv'][0])
                )
            );
            // set up our custom renderer for help options
            static::$commandParser->accept(new \Pyrus\ScriptFrontend\Renderer(static::$commandParser));
            // set up command-less options and argument
            static::$commandParser->addOption('verbose', array(
                'short_name'  => '-v',
                'long_name'   => '--verbose',
                'action'      => 'Counter',
                'description' => 'increase verbosity'
            ));
            static::$commandParser->addOption('paranoid', array(
                'short_name'  => '-p',
                'long_name'   => '--paranoid',
                'action'      => 'Counter',
                'description' => 'set or increase paranoia level'
            ));
            \Pyrus\PluginRegistry::registerFrontend($this);
            \Pyrus\PluginRegistry::addCommand($commands);
        }
        $term = getenv('TERM');
        if (function_exists('posix_isatty') && !posix_isatty(1)) {
            // output is being redirected to a file or through a pipe
        } elseif ($term) {
            if (preg_match('/^(xterm|vt220|linux)/', $term)) {
                $this->term['bold']   = sprintf("%c%c%c%c", 27, 91, 49, 109);
                $this->term['normal'] = sprintf("%c%c%c", 27, 91, 109);
            } elseif (preg_match('/^vt100/', $term)) {
                $this->term['bold']   = sprintf("%c%c%c%c%c%c", 27, 91, 49, 109, 0, 0);
                $this->term['normal'] = sprintf("%c%c%c%c%c", 27, 91, 109, 0, 0);
            }
        }
    }

    function mapCommand($commandinfo)
    {
        $command = static::$commandParser->addCommand($commandinfo['name'], array(
            'description' => $commandinfo['summary'],
            'aliases' => array($commandinfo['shortcut']),
        ));
        if (isset($commandinfo['options']['option'])) {
            $options = $commandinfo['options']['option'];
            if (!isset($options[0])) {
                $options = array($options);
            }
            foreach ($options as $option) {
                switch (key($option['type'])) {
                    case 'bool' :
                        $action = 'StoreTrue';
                        break;
                    case 'string' :
                        $action = 'StoreString';
                        break;
                    case 'int' :
                        $action = 'StoreInt';
                        break;
                    case 'float' :
                        $action = 'StoreFloat';
                        break;
                    case 'counter' :
                        $action = 'Counter';
                        break;
                    case 'callback' :
                        $func = $option['type']['callback'];
                        $class = $commandinfo['class'];
                        $callback = function ($value, $option, $result, $parser) use ($func, $class) {
                            return $class::$func($value);
                        };
                        $action = 'Callback';
                        break;
                    case 'set' :
                        $action = 'StoreString';
                        $choice = $option['set']['value'];
                        settype($choice, 'array');
                        break;
                }
                $info = array(
                    'short_name' => empty($option['shortopt']) ? null : '-' . $option['shortopt'],
                    'long_name' => empty($option['name']) ? null : '--' . $option['name'],
                    'description' => $option['doc'],
                    'action' => $action,
                );
                if ($action == 'Callback') {
                    $info['callback'] = $callback;
                }
                if (isset($option['default'])) {
                    $info['default'] = $option['default'];
                }
                if (isset($choice)) {
                    $info['choices'] = $choice;
                    $choice = null;
                }

                if ($info['long_name'] != null) {
                    $command->addOption(str_replace('-', '_', $option['name']), $info);
                } elseif ($info['shortopt'] != null) {
                    $command->addOption(str_replace('-', '_', $option['shortopt']), $info);
                } else {
                    throw new Exception('Invalid command ' . $commandinfo['name'] . ': No option name or shortopt was provided.');
                }
            }
        }
        if (isset($commandinfo['arguments']['argument'])) {
            $args = $commandinfo['arguments']['argument'];
            if (!isset($args[0])) {
                $args = array($args);
            }
            foreach ($args as $arg) {
                $command->addArgument($arg['name'], array(
                    'description' => $arg['doc'],
                    'multiple' => (bool) $arg['multiple'],
                    'optional' => (bool) $arg['optional'],
                ));
            }
        }
    }

    function _bold($text)
    {
        if (empty($this->term['bold'])) {
            return strtoupper($text);
        }

        return $this->term['bold'] . $text . $this->term['normal'];
    }

    function addDeveloperCommands($type)
    {
        $schemapath = \Pyrus\Main::getDataPath() . '/customcommand-2.0.xsd';
        $defaultcommands = \Pyrus\Main::getDataPath() . '/' . $type . 'commands.xml';
        if (!file_exists($schemapath)) {
            $schemapath = realpath(__DIR__ . '/../../../data/customcommand-2.0.xsd');
            $defaultcommands = realpath(__DIR__ . '/../../../data/' . $type . 'commands.xml');
        }
        $parser = new \Pyrus\XMLParser;
        $commands = $parser->parse($defaultcommands, $schemapath);
        $commands = $commands['commands']['command'];
        \Pyrus\PluginRegistry::addCommand($commands);
    }

    /**
     * This method acts as a controller which dispatches the request to the
     * correct command/method.
     *
     * <code>
     * $cli = \Pyrus\ScriptFrontend\Commands();
     * $cli->run($args = array (0 => 'install',
     *                          1 => 'PEAR2/Pyrus_Developer/package.xml'));
     * </code>
     *
     * The above code will dispatch to the install command
     *
     * @param array $args An array of command line arguments.
     *
     * @return void
     */
    function run($args)
    {
        try {
            $sig = \Pyrus\Main::getSignature();
            if ($sig) {
                echo "Pyrus version ", \Pyrus\Main::VERSION, ' ',
                     $sig['hash_type'], ': ', $sig['hash'], "\n";
            }

            $this->_findPEAR($args);
            $this->verbose = \Pyrus\Config::current()->verbose;

            // scan for custom commands/roles/tasks
            \Pyrus\Config::current()->pluginregistry->scan();

            if (!isset(static::$commandParser->commands['make'])) {
                $this->addDeveloperCommands('developer');
            }

            if (!isset(static::$commandParser->commands['scs-update'])) {
                $this->addDeveloperCommands('scs');
            }

            $result = static::$commandParser->parse(count($args) + 1, array_merge(array('cruft'), $args));
            if ($result->options['verbose']) {
                $this->verbose = $result->options['verbose'];
            }

            if ($result->options['paranoid']) {
                \Pyrus\Main::$paranoid = $result->options['paranoid'];
            }

            if ($info = \Pyrus\PluginRegistry::getCommandInfo($result->command_name)) {
                if ($this instanceof $info['class']) {
                    if ($info['function'] == 'dummyStub' || $info['function'] == 'scsDummyStub') {
                        $this->{$info['function']}($result);
                    } else {
                        $this->{$info['function']}($result->command->args, $result->command->options);
                    }
                } else {
                    $class = new $info['class'];
                    $class->{$info['function']}($this, $result->command->args, $result->command->options);
                }
            } else {
                $this->help(array('command' => isset($args[0]) ? $args[0] : null));
            }
        } catch (\PEAR2\Console\CommandLine\Exception $e) {
            static::$commandParser->displayError($e->getMessage(), false);
            if (
                   $e->getCode() == \PEAR2\Console\CommandLine\Exception::ARGUMENT_REQUIRED
                || $e->getCode() == \PEAR2\Console\CommandLine\Exception::OPTION_UNKNOWN) {
                $this->help(array('command' => $args[0]));
            } else {
                static::$commandParser->displayUsage(false);
            }
        }
    }

    function ask($question, array $choices = null, $default = null)
    {
        if (is_array($choices)) {
            foreach ($choices as $i => $choice) {
                if (is_int($i) && ($default === null || ($default !== null && !is_string($default)))) {
                    $is_int = false;
                } else {
                    $is_int = true;
                }
                break;
            }
        }
previous:
        echo $question,"\n";
        if ($choices !== null) {
            echo "Please choose:\n";
            foreach ($choices as $i => $choice) {
                if ($is_int) {
                    echo '  ',$choice,"\n";
                } else {
                    echo '  [',$i,'] ',$choice,"\n";
                }
            }
        }
        if ($default !== null) {
            echo '[',$default,']';
        }
        echo ' : ';
        $answer = $this->_readStdin();

        if (!strlen($answer)) {
            if ($default !== null) {
                $answer = $default;
            } else {
                $answer = null;
            }
        } elseif ($choices !== null) {
            if (($is_int && in_array($answer, $choices)) || (!$is_int && array_key_exists($answer, $choices))) {
                return $answer;
            } else {
                echo "Please choose one choice\n";
                goto previous;
            }
        }
        return $answer;
    }

    function _readStdin($amount = 1024)
    {
        return trim(fgets(STDIN, $amount));
    }

    protected function _findPEAR(&$arr)
    {
        $configclass = static::$configclass;
        if (isset($arr[0]) && @file_exists($arr[0]) && @is_dir($arr[0])) {
            $maybe = array_shift($arr);
            $maybe = realpath($maybe);
            echo "Using PEAR installation found at $maybe\n";
            $config = $configclass::singleton($maybe);
            return;
        }

        if (!$configclass::userInitialized()) {
            $this->_initializeConfiguration();
        }

        $config = $configclass::singleton();
        $path = $config->path;
        if (strpos($path, PATH_SEPARATOR)) {
            echo "Using PEAR installations found at $path\n";
        } else {
            echo "Using PEAR installation found at $path\n";
        }
    }

    protected function _initializeConfiguration()
    {
        echo "Pyrus: No user configuration file detected\n";

        if ('yes' !== $this->ask("It appears you have not used Pyrus before, welcome!  Initialize install?", array('yes', 'no'), 'yes')) {
            echo "OK, thank you, finishing execution now\n";
            exit;
        }

        $configclass = static::$configclass;
        echo "Great.  We will store your configuration in:\n  ",$configclass::getDefaultUserConfigFile(),"\n";
        previous:
        $path = $this->ask("Where would you like to install packages by default?", null, getcwd());
        echo "You have chosen:\n", $path, "\n";
        if (!realpath($path)) {
            echo " this path does not yet exist\n";
            if ('yes' !== $this->ask("Create it?", array('yes', 'no'), 'yes')) {
                goto previous;
            }
        } elseif (!is_dir($path)) {
            echo $path," exists, and is not a directory\n";
            goto previous;
        }

        $config = $configclass::singleton($path);
        $config->saveConfig();
        echo "Thank you, enjoy using Pyrus\n";
        echo "Documentation is at http://pear.php.net\n";
    }

    function dummyStub($command)
    {
        if ('yes' === $this->ask('The "' . $command->command_name .
                                 '" command is in the developer tools.  Install developer tools?',
                    array('yes', 'no'), 'no')) {
            return $this->upgrade(array('package' => array('pear2.php.net/PEAR2_Pyrus_Developer-alpha')),
                           array('plugin' => true, 'force' => false, 'optionaldeps' => false));
        }
    }

    function scsDummyStub($command)
    {
        if ('yes' === $this->ask('The "' . $command->command_name .
                                 '" command is in the simple channel server tools.  ' .
                                 'Install simple channel server tools?',
                    array('yes', 'no'), 'no')) {
            return $this->upgrade(array('package' => array('pear2.php.net/PEAR2_SimpleChannelServer-alpha')),
                           array('plugin' => true, 'force' => false, 'optionaldeps' => false));
        }
    }

    /**
     * Display the help dialog and list all commands supported.
     *
     * @param array $args Array of command line arguments
     */
    function help($args)
    {
        if (!isset($args['command']) || $args['command'] === 'help') {
            static::$commandParser->displayUsage();
        } else {
            $info = \Pyrus\PluginRegistry::getCommandInfo($args['command']);
            if (!$info) {
                foreach ($args as $arg) {
                    switch ($arg) {
                        case 'package' :
                        case 'make' :
                        case 'run-phpt' :
                        case 'pickle' :
                            if ('yes' === $this->ask('The "' . $arg .
                                                     '" command is in the developer tools.  Install developer tools?',
                                        array('yes', 'no'), 'no')) {
                                return $this->upgrade(array('package' =>
                                                            array('pear2.php.net/PEAR2_Pyrus_Developer-alpha')),
                                               array('plugin' => true, 'force' => false, 'optionaldeps' => false));
                            }
                        default :
                            break;
                    }
                }
                echo "Unknown command: $args[command]\n";
                static::$commandParser->displayUsage();
            } else {
                static::$commandParser->commands[$args['command']]->displayUsage();
                echo "\n", $info['doc'], "\n";
            }
        }
    }

    /**
     * install a local or remote package
     *
     * @param array $args
     */
    function install($args, $options)
    {
        if ($options['plugin']) {
            \Pyrus\Main::$options['install-plugins'] = true;
        }

        if ($options['force']) {
            \Pyrus\Main::$options['force'] = true;
        }

        if (isset($options['packagingroot']) && $options['packagingroot']) {
            \Pyrus\Main::$options['packagingroot'] = $options['packagingroot'];
        }

        if ($options['optionaldeps']) {
            \Pyrus\Main::$options['optionaldeps'] = $options['optionaldeps'];
        }

        \Pyrus\Installer::begin();
        try {
            $packages = array();
            foreach ($args['package'] as $arg) {
                \Pyrus\Installer::prepare($packages[] = new \Pyrus\Package($arg));
            }

            \Pyrus\Installer::commit();
            foreach (\Pyrus\Installer::getInstalledPackages() as $package) {
                echo 'Installed ' . $package->channel . '/' . $package->name . '-' .
                    $package->version['release'] . "\n";
                if ($package->type === 'extsrc' || $package->type === 'zendextsrc') {
                    echo " ==> To build this PECL package, use the build command\n";
                }
            }

            $optionals = \Pyrus\Installer::getIgnoredOptionalDeps();
            if (count($optionals)) {
                echo "Optional dependencies that will not be installed, use --optionaldeps:\n";
            }

            foreach ($optionals as $dep => $packages) {
                echo $dep, ' depended on by ', implode(', ', array_keys($packages)), "\n";
            }
        } catch (\Exception $e) {

            if ($e instanceof \Pyrus\Channel\Exception
                && strpos($arg, '/') === false) {
                echo "Sorry there was an error retrieving "
                    . \Pyrus\Config::current()->default_channel
                    . "/{$arg} from the default channel\n";
            }

            // If this is an undiscovered channel, handle it gracefully
            if ($e instanceof \Pyrus\Package\Exception
                && $e->getPrevious() instanceof \Pyrus\ChannelRegistry\Exception
                && $e->getPrevious()->getPrevious() instanceof \Pyrus\ChannelRegistry\ParseException
                && $e->getPrevious()->getPrevious()->why == 'channel'
                && strpos($arg, '/') !== false) {

                $channel = substr($arg, 0, strrpos($arg, '/'));

                echo "Sorry, the channel \"{$channel}\" is unknown.\n";

                return $this->installUnknownChannelExceptionHandler($args, $options, $e, $channel);
            }

            if ($e instanceof \Pyrus\ChannelRegistry\Exception
                && $e->getPrevious() instanceof \Pyrus\ChannelRegistry\ParseException
                && $e->getPrevious()->why == 'channel'
                // @todo Ugh, fix this mess
                && preg_match('/^unknown channel \"(.*)\" in \"(.*)\"$/', $e->getPrevious()->getMessage(), $matches)) {
                echo "Sorry, $arg references an unknown channel {$matches[1]} for {$matches[2]}\n";

                return $this->installUnknownChannelExceptionHandler($args, $options, $e, $matches[1]);
            }

            $this->exceptionHandler($e);
            exit(1);
        }
    }

    protected function installUnknownChannelExceptionHandler($args, $options, \Exception $e, $channel)
    {
        if ('yes' === $this->ask('Do you want to add this channel and continue?', array('yes', 'no'), 'yes')) {
            $this->channelDiscover(array('channel' => $channel));
            $this->install($args, $options);
            return;
        }
        echo "Ok. I understand.\n";
        $this->exceptionHandler($e);
        exit(1);
    }

    /**
     * uninstall an installed package
     *
     * @param array $args
     */
    function uninstall($args, $options)
    {
        if ($options['plugin']) {
            \Pyrus\Main::$options['install-plugins'] = true;
        }
        \Pyrus\Uninstaller::begin();
        $packages = $non = $failed = array();
        foreach ($args['package'] as $arg) {
            try {
                if (!isset(\Pyrus\Config::current()->registry->package[$arg])) {
                    $non[] = $arg;
                    continue;
                }
                $packages[] = \Pyrus\Uninstaller::prepare($arg);
            } catch (\Exception $e) {
                $failed[] = $arg;
            }
        }
        \Pyrus\Uninstaller::commit();
        foreach ($non as $package) {
            echo "Package $package not installed, cannot uninstall\n";
        }
        foreach ($packages as $package) {
            echo 'Uninstalled ', $package->channel, '/', $package->name, "\n";
        }
        foreach ($failed as $package) {
            echo "Package $package could not be uninstalled\n";
        }
    }

    /**
     * download a remote package
     *
     * @param array $args
     */
    function download($args)
    {
        \Pyrus\Main::$options['downloadonly'] = true;
        \Pyrus\Config::current()->download_dir = getcwd();
        $packages = array();
        foreach ($args['package'] as $arg) {
            try {
                $packages[] = array(new \Pyrus\Package($arg), $arg);
            } catch (\Exception $e) {
                echo "failed to init $arg for download (", $e->getMessage(), ")\n";
            }
        }
        foreach ($packages as $package) {
            $arg = $package[1];
            $package = $package[0];
            echo "Downloading ", $arg, '...';
            try {
                if ($package->isRemote()) {
                    $package->download();
                } else {
                    $package->copyTo(getcwd());
                }
                $path = $package->getInternalPackage()->getTarballPath();
                echo "\ndone ($path)\n";
            } catch (\Exception $e) {
                echo 'failed! (', $e->getMessage(), ")\n";
            }
        }
    }

    /**
     * Upgrade a package
     *
     * @param array $args
     */
    function upgrade($args, $options)
    {
        \Pyrus\Main::$options['upgrade'] = true;
        $this->install($args, $options);
    }

    /**
     * list all the installed packages
     *
     * @param array $args
     */
    function listPackages()
    {
        $reg = \Pyrus\Config::current()->registry;
        $creg = \Pyrus\Config::current()->channelregistry;
        $cascade = array(array($reg, $creg));
        $p = $reg;
        $c = $creg;
        while ($p = $p->getParent()) {
            $c = $c->getParent();
            $cascade[] = array($p, $c);
        }
        array_reverse($cascade);
        foreach ($cascade as $p) {
            $c = $p[1];
            $p = $p[0];
            echo "Listing installed packages [", $p->getPath(), "]:\n";
            $packages = array();
            foreach ($c as $channel) {
                \Pyrus\Config::current()->default_channel = $channel->name;
                foreach ($p->package as $package) {
                    $packages[$channel->name][$package->name] = $package;
                }
            }
            asort($packages);
            foreach ($packages as $channel => $channel_packages) {
                echo "[channel $channel]:\n";
                ksort($channel_packages);
                foreach ($channel_packages as $package) {
                    $data = array($package->name,
                                  $package->version['release'],
                                  $package->stability['release'],
                                  );
                    // @TODO add CLI table output
                    echo implode($data, ' ') . PHP_EOL;
                }
            }
        }
    }

    /**
     * List all the known channels
     *
     * @param array $args
     */
    function listChannels()
    {
        $creg = \Pyrus\Config::current()->channelregistry;
        $cascade = array($creg);
        while ($c = $creg->getParent()) {
            $cascade[] = $c;
            $creg = $c;
        }
        array_reverse($cascade);
        foreach ($cascade as $c) {
            echo "Listing channels [", $c->getPath(), "]:\n";
            $chans = array();
            foreach ($c as $channel) {
                $chans[$channel->name] = $channel->alias;
            }
            ksort($chans);
            foreach ($chans as $channel => $alias) {
                echo $channel . ' (' . $alias . ")\n";
            }
        }
    }

    /**
     * remotely connect to a channel server and grab the channel information,
     * then add it to the current pyrus managed repo
     *
     * @param array $args $args[0] should be the channel name, eg:pear.unl.edu
     */
    function channelDiscover($args)
    {
        try {
            $channel = new \Pyrus\ChannelFile($args['channel'], false, true);
        } catch (\Exception $e) {
            echo "Discovery of channel ", $args['channel'], " failed: ", $e->getMessage(), "\n";
            return;
        }

        $chan = new \Pyrus\Channel($channel);
        \Pyrus\Config::current()->channelregistry->add($chan);
        echo "Discovery of channel ", $chan->name, " successful\n";
    }

    /**
     * add a channel to the current pyrus managed path using the raw channel.xml
     *
     * @param array $args $args[0] should be the channel.xml filename
     */
    function channelAdd($args)
    {
        echo "Adding channel from channel.xml:\n";
        $chan = new \Pyrus\Channel(new \Pyrus\ChannelFile($args['channelfile']));
        \Pyrus\Config::current()->channelregistry->add($chan);
        echo "Adding channel ", $chan->name, " successful\n";
    }

    function channelDel($args)
    {
        $chan = \Pyrus\Config::current()->channelregistry->get($args['channel'], false);
        if (count(\Pyrus\Config::current()->registry->listPackages($chan->name))) {
            echo "Cannot remove channel ", $chan->name, " packages are installed\n";
            exit(1);
        }
        \Pyrus\Config::current()->channelregistry->delete($chan);
        echo "Deleting channel ", $chan->name, " successful\n";
    }

    function upgradeRegistry($args, $options)
    {
        if (!file_exists($args['path']) || !is_dir($args['path'])) {
            echo "Cannot upgrade registries at ", $args['path'], ", path does not exist or is not a directory\n";
            exit(1);
        }
        echo "Upgrading registry at path ", $args['path'], "\n";
        $registries = \Pyrus\Registry::detectRegistries($args['path']);
        if (!count($registries)) {
            echo "No registries found\n";
            exit;
        }
        if (!in_array('Pear1', $registries)) {
            echo "Registry already upgraded\n";
            exit;
        }
        $pear1 = new \Pyrus\Registry\Pear1($args['path']);
        if (!in_array('Sqlite3', $registries)) {
            $sqlite3 = new \Pyrus\Registry\Sqlite3($args['path']);
            $sqlite3->cloneRegistry($pear1);
        }
        if (!in_array('Xml', $registries)) {
            $xml = new \Pyrus\Registry\Xml($args['path']);
            $sqlite3 = new \Pyrus\Registry\Sqlite3($args['path']);
            $xml->cloneRegistry($sqlite3);
        }
        if ($options['removeold']) {
            \Pyrus\Registry\Pear1::removeRegistry($args['path']);
        }
    }

    function runScripts($args)
    {
        $runner = new \Pyrus\ScriptRunner($this);
        $reg = \Pyrus\Config::current()->registry;
        foreach ($args['package'] as $package) {
            $package = $reg->package[$package];
            $runner->run($package);
        }
    }

    /**
     * Display pyrus configuration vars
     *
     */
    function configShow($args, $options)
    {
        $conf = $current = \Pyrus\Config::current();
        if ($options['plugin']) {
            echo "Plugin configuration:\n";
            $conf = \Pyrus\Config::singleton(\Pyrus\Config::current()->plugins_dir);
        }
        echo "System paths:\n";
        foreach ($conf->mainsystemvars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "Custom System paths:\n";
        foreach ($conf->customsystemvars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "User config (from ", $conf->userfile, "):\n";
        foreach ($conf->mainuservars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "(variables specific to ", $conf->default_channel, "):\n";
        foreach ($conf->mainchannelvars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "Custom User config (from " . $conf->userfile . "):\n";
        foreach ($conf->customuservars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "(variables specific to ", $conf->default_channel, "):\n";
        foreach ($conf->customchannelvars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
    }

    /**
     * Get a configuration option.
     *
     * @param array $args
     */
    function get($args, $options)
    {
        $conf = $current = \Pyrus\Config::current();
        if ($options['plugin']) {
            $conf = \Pyrus\Config::singleton(\Pyrus\Config::current()->plugins_dir);
        }
        if (in_array($args['variable'], $conf->uservars)
            || in_array($args['variable'], $conf->systemvars)) {
            echo $conf->{$args['variable']} . PHP_EOL;
        } else {
            echo "Unknown config variable: $args[variable]\n";
            exit(1);
        }
        if ($options['plugin']) {
            \Pyrus\Config::setCurrent($current->path);
        }
    }

    /**
     * Set a configuration option.
     *
     * @param array $args
     */
    function set($args, $options)
    {
        $conf = $current = \Pyrus\Config::current();
        if ($options['plugin']) {
            $conf = \Pyrus\Config::singleton(\Pyrus\Config::current()->plugins_dir);
        }
        if (in_array($args['variable'], $conf->uservars)) {
            echo "Setting $args[variable] in " . $conf->userfile . "\n";
            $conf->{$args['variable']} = $args['value'];
        } elseif (in_array($args['variable'], $conf->systemvars)) {
            echo "Setting $args[variable] in system paths\n";
            $conf->{$args['variable']} = $args['value'];
        } else {
            echo "Unknown config variable: $args[variable]\n";
            exit(1);
        }
        $conf->saveConfig();
        if ($options['plugin']) {
            \Pyrus\Config::setCurrent($current->path);
        }
    }

    /**
     * Set up a pear path managed by pyrus.
     *
     * @param array $args Arguments
     */
    function mypear($args)
    {
        echo "Setting my pear repositories to:\n";
        echo implode("\n", $args['path']) . "\n";
        $args = implode(PATH_SEPARATOR, $args['path']);
        \Pyrus\Config::current()->my_pear_path = $args;
        \Pyrus\Config::current()->saveConfig();
    }

    function build($args)
    {
        echo "Building PECL extensions\n";
        $builder = new \Pyrus\PECLBuild($this);
        foreach ($args['PackageName'] as $arg) {
            $package = \Pyrus\Config::current()->registry->package[$arg];
            $builder->installBuiltStuff($package, $builder->build($package));
        }
    }

    function info($args, $options)
    {
        if (!$options['forceremote']) {
            if (file_exists($args['package'])) {
                $package = new \Pyrus\Package($args['package']);
                $installed = false;
            } elseif (isset(\Pyrus\Config::current()->registry->package[$args['package']])) {
                $package = \Pyrus\Config::current()->registry->package[$args['package']];
                $installed = true;
            }
        }
        if (!isset($package)) {
            $installed = false;
            $package = new \Pyrus\Package($args['package'], $options['forceremote']);
        }
        echo $this->wrap($package->name . ' (' . $package->channel . ' Channel)'), "\n";
        echo str_repeat('-', 80), "\n";
        // this next line ensures we get an accurate reading on a remote abstract package
        if (!$installed && $package->isRemote()) {
            $package->grabEntirePackagexml();
        }
        if (!isset($args['field'])) {
            echo 'Package type: ';
            switch ($package->type) {
                case 'php' :
                    echo "PHP package\n";
                    break;
                case 'extsrc' :
                    echo "Extension source package\n";
                    break;
                case 'zendextsrc' :
                    echo "Zend Extension source package\n";
                    break;
                case 'extbin' :
                    echo "Extension binary package\n";
                    break;
                case 'zendextbin' :
                    echo "Zend Extension binary package\n";
                    break;
                case 'bundle' :
                    echo "Package Bundle\n";
                    break;
            }
            echo 'Version:      ', $package->version['release'], ' (API ', $package->version['api'], "), ";
            echo 'Stability:    ', $package->stability['release'], ' (API ', $package->stability['api'], ")\n";
            echo 'Release Date: ', $package->date;
            if ($package->time) {
                echo ' ', $package->time;
            }
            echo "\n";
            echo "Package Summary: ", $this->columnWrap($package->summary, strlen("Package Summary: ")), "\n";
            echo "Package Description Excerpt:\n   ",
                 $this->columnWrap(substr(rtrim($package->description), 0, 171) . '...', 3), "\n";
            echo '(`php pyrus.phar info ' . $args['package'] . " description` for full description)\n";
            echo "Release Notes Excerpt:\n   ",
                $this->columnWrap(substr(rtrim($package->notes), 0, 171) . '...', 3), "\n";
            echo '(`php pyrus.phar info ' . $args['package'] . " notes` for full release notes)\n";
            if ($installed) {
                // check for upgrades
                try {
                    $tester = new \Pyrus\Package($package->channel . '/' . $package->name, true);
                    $upgrades = $tester->getAllUpgrades($package->version['release']);
                    if (count($upgrades)) {
                        echo "Upgrades available:\n";
                        foreach ($upgrades as $info) {
                            echo '  Version ', $info['v'], ' (', $info['s'], ")\n";
                        }
                    }
                } catch (\Exception $e) {
                    // ignore problems here, no need to freak out if checking for upgrades fails
                    if ($this->verbose > 3) {
                        echo $e;
                    }
                }
            }
        } elseif ($args['field'] == 'description') {
            echo "Package Description:\n   ", $this->columnWrap(trim($package->description), 3), "\n";
        } elseif ($args['field'] == 'notes') {
            echo "Release Notes:\n   ", $this->columnWrap($package->notes, 3), "\n";
        } elseif ($args['field'] == 'files') {
            if ($installed) {
                echo "Package Files (installed):\n";
                foreach (\Pyrus\Config::current()->registry->info($package->name, $package->channel,
                                                                       'installedfiles') as $file => $info) {
                    echo $file, ' (', $info['role'], ")\n";
                }
            } else {
                if ($package->isRemote()) {
                    echo "Package Files:\n";
                    foreach ($package->contents as $file) {
                        echo $file->name, ' (', $file->role, ")\n";
                    }
                } else {
                    echo "Package Files (as would be installed):\n";
                    foreach ($package->installcontents as $file) {
                        echo $file->name, ' (', $file->role, ")\n";
                    }
                }
            }
        } else {
            echo "Unknown sub-field ", $args['field'], " must be one of description, notes, or files\n";
        }
    }

    function listUpgrades()
    {
        $config = \Pyrus\Config::current();
        $reg = $config->registry;
        foreach ($config->channelregistry as $channel) {
            $packages = $reg->listPackages($channel->name);
            if (!count($packages)) {
                echo "(no packages installed in channel ", $channel->name, ")\n";
                continue;
            }
            $upgrades = array();
            foreach ($packages as $package) {
                try {
                    $version = $reg->info($package, $channel->name, 'version');
                    $tester = $channel->remotepackage[$package];
                    // find a version newer than us
                    $fakedep = new \Pyrus\PackageFile\v2\Dependencies\Package(
                        'required', 'package', null, array('name' => $package,
                                            'channel' => $channel->name, 'uri' => null,
                                            'min' => $version, 'max' => null,
                                            'recommended' => null, 'exclude' => array($version),
                                            'providesextension' => null, 'conflicts' => null), 0);
                    $tester->figureOutBestVersion($fakedep);
                } catch (\Exception $e) {
                    continue;
                }
                $upgrades[$package] = array($tester->version['release'], $tester->stability['release'], $tester->date);
            }
            if (!count($upgrades)) {
                echo "(no upgrades for packages installed in channel ", $channel->name, ")\n";
                continue;
            }
            asort($upgrades);
            echo "Upgrades for channel ", $channel->name, ":\n";
            foreach ($upgrades as $package => $upgrade) {
                echo '  ', $package, ' ', $upgrade[0], ' (' . $upgrade[1], ", released ", $upgrade[2], ")\n";
            }
        }
    }

    function listAll($args, $options)
    {
        $reg = \Pyrus\Config::current()->registry;
        echo "Remote packages for channel ", $args['channel'], ":\n";
        if ($options['basic']) {
            foreach (\Pyrus\Config::current()->channelregistry[$args['channel']]->remotecategories as $category) {
                echo $category->name, ":\n";
                foreach ($category->basiclist as $package) {
                    $installed = $reg->exists($package['package'], $args['channel']) ? '  *' : '   ';
                    echo $installed, $package['package'], ' latest stable: ', $package['stable'],
                        ', latest release: ', $package['latest']['v'], ' (', $package['latest']['s'], ")\n";
                }
            }
            return;
        }
        foreach (\Pyrus\Config::current()->channelregistry[$args['channel']]->remotecategories as $category) {
            echo $category->name, ":\n";
            $pnames = array();
            $summaries = array();
            $pnameinfo = array();
            $versions = array();
            try {
                foreach ($category as $package) {
                    $installed = ' ';
                    if ($package->isUpgradeable()) {
                        $installed = '!';
                    } elseif ($reg->exists($package->name, $args['channel'])) {
                        $installed = '*';
                    }
                    $found = false;
                    foreach ($package as $version => $latest) {
                        $found = true;
                        break;
                    }
                    $pnames[] = $package->name;
                    $summaries[] = $package->summary;
                    if (!$found) {
                        $versions[] = '--';
                        $pnameinfo[$package->name] = array('installed' => $installed,
                                                           'summary' => $package->summary,
                                                           'latest' => 'n/a');
                        continue;
                    }
                    $versions[] = $version;
                    $latest['v'] = $version;

                    $pnameinfo[$package->name] = array('installed' => $installed,
                                                       'summary' => $package->summary,
                                                       'latest' => $latest);
                }
            } catch (\Exception $e) {
                echo "Error: Category has broken REST (", $e->getMessage(), ")\n";
                continue;
            }
            $widths = array(1, 25, 8, 51);
            foreach ($pnameinfo as $package => $info) {
                if (is_string($info['latest'])) {
                    $text = array($info['installed'], $package, $info['latest'], $info['summary']);
                } else {
                    $text = array($info['installed'], $package, $info['latest']['v'], $info['summary']);
                }
                echo $this->wrapMultiColumns($text, $widths) . "\n";
            }
            echo "Key: * = installed, ! = upgrades available\n";
        }
    }

    protected function wrap($text)
    {
        return wordwrap($text, 80, "\n", false);
    }

    /**
     * Borrowed from PEAR2_Console_CommandLine
     */
    protected function columnWrap($text, $cw)
    {
        $tokens = explode("\n", $this->wrap($text));
        $ret    = $tokens[0];
        $chunks = $this->wrap(trim(substr($text, strlen($ret))), 80 - $cw);
        $tokens = explode("\n", $chunks);
        foreach ($tokens as $token) {
            if (!empty($token)) {
                $ret .= "\n" . str_repeat(' ', $cw) . $token;
            }
        }
        return $ret;
    }
    static function wrapMultiColumns($text, $widths)
    {
        $max = 0;
        foreach ($text as $col => $cell) {
            $text[$col] = explode("\n", wordwrap($cell, $widths[$col], "\n", false));
            $newtext = array();
            foreach ($text[$col] as $subcell) {
                if (strlen($subcell) > $widths[$col]) {
                    $split = explode("\n", wordwrap($subcell, $widths[$col], "\\\n", true));
                    foreach ($split as $subcell) {
                        $newtext[] = $subcell;
                    }
                } else {
                    $newtext[] = $subcell;
                }
                $text[$col] = $newtext;
            }
            if (count($text[$col]) > $max) {
                $max = count($text[$col]);
            }
        }

        $ret = '';
        for ($i = 0; $i < $max; $i++) {
            if ($ret) {
                $ret .= "\n";
            }
            foreach ($text as $col => $cell) {
                if ($col && !$previousWasLong) {
                    $ret .= ' ';
                }
                if ($col) {
                    $ret .= ' ';
                }
                if (isset($cell[$i])) {
                    $ret .= str_pad($cell[$i], $widths[$col], ' ');
                    if (strlen($cell[$i]) > $widths[$col]) {
                        $previousWasLong = true;
                    } else {
                        $previousWasLong = false;
                    }
                } else {
                    $ret .= str_repeat(' ', $widths[$col]);
                    $previousWasLong = false;
                }
            }
        }
        return $ret;
    }

    function __call($func, $params)
    {
        if ($func === 'confirmDialog') {
            return $this->_confirmDialog($params[0]);
        }
        if ($func === 'display') {
            return $this->_display($params[0]);
        }
        if ($func === 'ask') {
            return call_user_func_array(array($this, 'ask'), $params);
        }
        throw new \Exception('Unknown method ' . $func . ' in class Pyrus\ScriptFrontend\Commands');
    }

    /**
     * Ask for user input, confirm the answers and continue until the user is satisfied
     * @param array an array of arrays, format array('name' => 'paramname', 'prompt' =>
     *              'text to display', 'type' => 'string'[, default => 'default value'])
     * @return array
     */
    function _confirmDialog($params)
    {
        $answers = $prompts = $types = array();
        foreach ($params as $param) {
            $prompts[$param['name']] = $param['prompt'];
            $types[$param['name']]   = $param['type'];
            $answers[$param['name']] = isset($param['default']) ? $param['default'] : '';
        }

        $tried = false;
        do {
            if ($tried) {
                $i = 1;
                foreach ($answers as $var => $value) {
                    if (!strlen($value)) {
                        echo $this->_bold("* Enter an answer for #" . $i . ": ({$prompts[$var]})\n");
                    }
                    $i++;
                }
            }

            $answers = $this->_userDialog('', $prompts, $types, $answers);
            $tried   = true;
        } while (is_array($answers) && count(array_filter($answers)) != count($prompts));

        return $answers;
    }

    function _display($text)
    {
        echo $text, "\n";
    }

    function _userDialog($command, $prompts, $types = array(), $defaults = array(), $screensize = 20)
    {
        if (!is_array($prompts)) {
            return array();
        }

        $testprompts = array_keys($prompts);
        $result      = $defaults;

        reset($prompts);
        if (count($prompts) === 1) {
            foreach ($prompts as $key => $prompt) {
                $type    = $types[$key];
                $default = isset($defaults[$key]) ? $defaults[$key] : false;
                print "$prompt ";
                if ($default) {
                    print "[$default] ";
                }
                print ": ";

                $line         = $this->_readStdin(2048);
                $result[$key] =  ($default && trim($line) == '') ? $default : trim($line);
            }

            return $result;
        }

        $first_run = true;
        while (true) {
            $descLength = max(array_map('strlen', $prompts));
            $descFormat = "%-{$descLength}s";
            $last       = count($prompts);

            $i = 0;
            foreach ($prompts as $n => $var) {
                $res = isset($result[$n]) ? $result[$n] : null;
                printf("%2d. $descFormat : %s\n", ++$i, $prompts[$n], $res);
            }
            print "\n1-$last, 'all', 'abort', or Enter to continue: ";

            $tmp = $this->_readStdin();
            if (empty($tmp)) {
                break;
            }

            if ($tmp == 'abort') {
                return false;
            }

            if (isset($testprompts[(int)$tmp - 1])) {
                $var     = $testprompts[(int)$tmp - 1];
                $desc    = $prompts[$var];
                $current = @$result[$var];
                print "$desc [$current] : ";
                $tmp = $this->_readStdin();
                if ($tmp !== '') {
                    $result[$var] = $tmp;
                }
            } elseif ($tmp == 'all') {
                foreach ($prompts as $var => $desc) {
                    $current = $result[$var];
                    print "$desc [$current] : ";
                    $tmp = $this->_readStdin();
                    if (trim($tmp) !== '') {
                        $result[$var] = trim($tmp);
                    }
                }
            }

            $first_run = false;
        }

        return $result;
    }

    function log($level, $message)
    {
        static $data = array();
        if (\Pyrus\Config::initializing()) {
            // we can't check verbose until initializing is complete, so save
            // the message, and only display the log after config is initialized
            $data[] = array($level, $message);
            return;
        }

        if (count($data)) {
            $save = $data;
            $data = array();
            foreach ($save as $info) {
                $this->log($info[0], $info[1]);
            }
        }

        if ($level <= $this->verbose) {
            if (strlen($message) && $message[strlen($message)-1] !== "\r") {
                echo $message . "\n";
            } else {
                echo $message;
            }
        }
    }
}
