<?php
/**
 * This script handles the command line interface commands to Pyrus
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
 * This script handles the command line interface commands to Pyrus
 *
 * Each command is a separate method, and will be called with the arguments
 * entered by the end user.
 *
 * @category  PEAR2
 * @package   PEAR2_Pyrus
 * @author    Greg Beaver <cellog@php.net>
 * @copyright 2008 The PEAR Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://svn.pear.php.net/wsvn/PEARSVN/Pyrus/
 */
class PEAR2_Pyrus_ScriptFrontend_Commands
{
    public $commands = array();
    // for unit-testing ease
    public static $configclass = 'PEAR2_Pyrus_Config';

    function __construct()
    {
        $a = new ReflectionClass($this);
        foreach ($a->getMethods() as $method) {
            $name = $method->name;
            if ($name[0] == '_' || $name === 'run') {
                continue;
            }
            $this->commands[preg_replace_callback('/[A-Z]/',
                    function($m) {return "-" . strtolower($m[0]);}, $name)] = $name;
        }
    }

    /**
     * This method acts as a controller which dispatches the request to the
     * correct command/method.
     *
     * <code>
     * $cli = PEAR2_Pyrus_ScriptFrontend_Commands();
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
            if (!count($args)) {
                $args[0] = 'help';
            }
            $this->_findPEAR($args);
            if (isset($this->commands[$args[0]])) {
                $command = array_shift($args);
                $command = $this->commands[$command];
                $this->$command($args);
            } else {
                $this->help($args);
            }
        } catch (Exception $e) {
            echo "Operation failed:\n$e";
        }
    }

    function _ask($question, array $choices = null, $default = null)
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

    function _readStdin()
    {
        return trim(fgets(STDIN, 1024));
    }

    function _findPEAR(&$arr)
    {
        if (isset($arr[0]) && @file_exists($arr[0]) && @is_dir($arr[0])) {
            $maybe = array_shift($arr);
            $maybe = realpath($maybe);
            echo "Using PEAR installation found at $maybe\n";
            $configclass = static::$configclass;
            $config = $configclass::singleton($maybe);
            return;
        }
        $configclass = static::$configclass;
        if (!$configclass::userInitialized()) {
            echo "Pyrus: No user configuration file detected\n";
            if ('yes' === $this->_ask("It appears you have not used Pyrus before, welcome!  Initialize install?", array('yes', 'no'), 'yes')) {
                echo "Great.  We will store your configuration in:\n  ",$configclass::getDefaultUserConfigFile(),"\n";
previous:
                $path = $this->_ask("Where would you like to install packages by default?", null, getcwd());
                echo "You have chosen:\n", $path, "\n";
                if (!realpath($path)) {
                    echo " this path does not yet exist\n";
                    if ('yes' !== $this->_ask("Create it?", array('yes', 'no'), 'yes')) {
                        goto previous;
                    }
                } elseif (!is_dir($path)) {
                    echo $path," exists, and is not a directory\n";
                    goto previous;
                }
                $configclass = static::$configclass;
                $config = $configclass::singleton($path);
                $config->saveConfig();
                echo "Thank you, enjoy using Pyrus\n";
                echo "Documentation is at http://pear.php.net\n";
            } else {
                echo "OK, thank you, finishing execution now\n";
                exit;
            }
        }
        $configclass = static::$configclass;
        $mypath = $configclass::current()->my_pear_path;
        if ($mypath) {
            foreach (explode(PATH_SEPARATOR, $mypath) as $path) {
                echo "Using PEAR installation found at $path\n";
                $configclass = static::$configclass;
                $config = $configclass::singleton($path);
                return;
            }
        }
    }

    /**
     * Display the help dialog and list all commands supported.
     *
     * @param array $args Array of command line arguments
     */
    function help($args)
    {
        if (isset($args[0]) && $args[0] == 'help') {
            echo "Commands supported:\n";
            foreach ($this->commands as $command => $true) {
                echo "$command\n";
            }
        } else {
            if (isset($args[0])) {
                echo "Unknown command: $args[0]\n";
            }
            echo "Commands supported:\n";
            foreach ($this->commands as $command => $true) {
                echo "$command [PEARPath]\n";
            }
        }
    }

    /**
     * install a local or remote package
     *
     * @param array $args
     */
    function install($args)
    {
        PEAR2_Pyrus_Installer::begin();
        try {
            $packages = array();
            foreach ($args as $arg) {
                PEAR2_Pyrus_Installer::prepare($packages[] = new PEAR2_Pyrus_Package($arg));
            }
            PEAR2_Pyrus_Installer::commit();
            foreach (PEAR2_Pyrus_Installer::getInstalledPackages() as $package) {
                echo 'Installed ' . $package->channel . '/' . $package->name . '-' .
                    $package->version['release'] . "\n";
            }
        } catch (Exception $e) {
            echo $e;
            exit -1;
        }
    }

    /**
     * uninstall an installed package
     *
     * @param array $args
     */
    function uninstall($args)
    {
        PEAR2_Pyrus_Uninstaller::begin();
        try {
            $packages = $non = $failed = array();
            foreach ($args as $arg) {
                try {
                    if (!isset(PEAR2_Pyrus_Config::current()->registry->package[$arg])) {
                        $non[] = $arg;
                        continue;
                    }
                    $packages[] = PEAR2_Pyrus_Uninstaller::prepare($arg);
                } catch (Exception $e) {
                    $failed[] = $arg;
                }
            }
            PEAR2_Pyrus_Uninstaller::commit();
            foreach ($non as $package) {
                echo "Package $package not installed, cannot uninstall\n";
            }
            foreach ($packages as $package) {
                echo 'Uninstalled ', $package->channel, '/', $package->name, "\n";
            }
            foreach ($failed as $package) {
                echo "Package $package could not be uninstalled\n";
            }
        } catch (Exception $e) {
            echo $e;
            exit -1;
        }
    }

    /**
     * download a remote package
     *
     * @param array $args
     */
    function download($args)
    {
        PEAR2_Pyrus_Config::current()->download_dir = getcwd();
        $packages = array();
        foreach ($args as $arg) {
            try {
                $packages[] = array(new PEAR2_Pyrus_Package($arg), $arg);
            } catch (Exception $e) {
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
                echo "done ($path)\n";
            } catch (Exception $e) {
                echo 'failed! (', $e->getMessage(), ")\n";
            }
        }
    }

    /**
     * Upgrade a package
     *
     * @param array $args
     */
    function upgrade($args)
    {
        PEAR2_Pyrus_Installer::$options['upgrade'] = true;
        $this->install($args);
    }

    /**
     * list all the installed packages
     *
     * @param array $args
     */
    function listPackages($args)
    {
        $reg = PEAR2_Pyrus_Config::current()->registry;
        $creg = PEAR2_Pyrus_Config::current()->channelregistry;
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
                PEAR2_Pyrus_Config::current()->default_channel = $channel->name;
                foreach ($p->package as $package) {
                    $packages[$channel->name][] = $package->name;
                }
            }
            asort($packages);
            foreach ($packages as $channel => $stuff) {
                echo "[channel $channel]:\n";
                foreach ($stuff as $package) {
                    echo " $package\n";
                }
            }
        }
    }

    /**
     * List all the known channels
     *
     * @param array $args
     */
    function listChannels($args)
    {
        $creg = PEAR2_Pyrus_Config::current()->channelregistry;
        $cascade = array($creg);
        while ($c = $creg->getParent()) {
            $cascade[] = $c;
            $creg = $c;
        }
        array_reverse($cascade);
        foreach ($cascade as $c) {
            echo "Listing channels [", $c->getPath(), "]:\n";
            foreach ($c as $channel) {
                echo $channel->name . ' (' . $channel->alias . ")\n";
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
        $chan = 'http://' . $args[0] . '/channel.xml';
        $http = new PEAR2_HTTP_Request($chan);
        try {
            $response = $http->sendRequest();
        } catch (Exception $e) {
            // try secure
            try {
                $chan = 'https://' . $args[0] . '/channel.xml';
                $http = new PEAR2_HTTP_Request($chan);
                $response = $http->sendRequest();
            } catch (Exception $u) {
                // failed, re-throw original error
                throw $e;
            }
        }

        $chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile($response->body, true));
        PEAR2_Pyrus_Config::current()->channelregistry->add($chan);
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
        $chan = new PEAR2_Pyrus_Channel(new PEAR2_Pyrus_ChannelFile($args[0]));
        PEAR2_Pyrus_Config::current()->channelregistry->add($chan);
        echo "Adding channel ", $chan->name, " successful\n";
    }

    function channelDel($args)
    {
        $chan = PEAR2_Pyrus_Config::current()->channelregistry->get($args[0], false);
        if (count(PEAR2_Pyrus_Config::current()->registry->listPackages($chan->name))) {
            echo "Cannot remove channel ", $chan->name, " packages are installed\n";
            exit -1;
        }
        PEAR2_Pyrus_Config::current()->channelregistry->delete($chan);
        echo "Deleting channel ", $chan->name, " successful\n";
    }

    function upgradeRegistry($args)
    {
        if (!file_exists($args[0]) || !is_dir($args[0])) {
            echo "Cannot upgrade registries at ", $args[0], ", path does not exist or is not a directory\n";
            exit -1;
        }
        echo "Upgrading registry at path ", $args[0], "\n";
        $registries = PEAR2_Pyrus_Registry::detectRegistries($args[0]);
        if (!count($registries)) {
            echo "No registries found\n";
            exit;
        }
        if (!in_array('Pear1', $registries)) {
            echo "Registry already upgraded\n";
            exit;
        }
        $pear1 = new PEAR2_Pyrus_Registry_Pear1($args[0]);
        if (!in_array('Sqlite3', $registries)) {
            $sqlite3 = new PEAR2_Pyrus_Registry_Sqlite3($args[0]);
            $sqlite3->cloneRegistry($pear1);
        }
        if (!in_array('Xml', $registries)) {
            $xml = new PEAR2_Pyrus_Registry_Xml($args[0]);
            $sqlite3 = new PEAR2_Pyrus_Registry_Sqlite3($args[0]);
            $xml->cloneRegistry($sqlite3);
        }
        if (isset($args[1]) && $args[1] == '--removeold') {
            PEAR2_Pyrus_Registry_Pear1::removeRegistry($args[0]);
        }
    }

    function runScripts($args)
    {
        $runner = new PEAR2_Pyrus_ScriptRunner($this);
        $reg = PEAR2_Pyrus_Config::current()->registry;
        foreach ($args as $package) {
            $package = $reg->package[$package];
            $runner->run($package);
        }
    }

    /**
     * Display pyrus configuration vars
     *
     * @param array $args
     */
    function configShow($args)
    {
        $conf = PEAR2_Pyrus_Config::current();
        echo "System paths:\n";
        foreach ($conf->mainsystemvars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "Custom System paths:\n";
        foreach ($conf->customsystemvars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "User config (from " . $conf->userfile . "):\n";
        foreach ($conf->mainuservars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
        echo "Custom User config (from " . $conf->userfile . "):\n";
        foreach ($conf->customuservars as $var) {
            echo "  $var => " . $conf->$var . "\n";
        }
    }

    /**
     * Set a configuration option.
     *
     * @param array $args
     */
    function set($args)
    {
        $conf = PEAR2_Pyrus_Config::current();
        if (in_array($args[0], $conf->uservars)) {
            echo "Setting $args[0] in " . $conf->userfile . "\n";
            $conf->{$args[0]} = $args[1];
        } elseif (in_array($args[0], $conf->systemvars)) {
            echo "Setting $args[0] in system paths\n";
            $conf->{$args[0]} = $args[1];
        } else {
            echo "Unknown config variable: $args[0]\n";
            exit -1;
        }
        $conf->saveConfig();
    }

    /**
     * Set up a pear path managed by pyrus.
     *
     * @param array $args Arguments
     */
    function mypear($args)
    {
        echo "Setting my pear repositories to:\n";
        echo implode("\n", $args) . "\n";
        $args = implode(PATH_SEPARATOR, $args);
        PEAR2_Pyrus_Config::current()->my_pear_path = $args;
        PEAR2_Pyrus_Config::current()->saveConfig();
    }
}
